<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

class NatigaController extends Controller{

    public function result(Request $request){
        $results = cache()->remember('slug_'.$this->slug($request->seating_no), 3600, function ()use($request) {
            return \App\Models\Result::where('total_results','<',410)->where(function($q)use($request){
                    if(is_numeric($request->seating_no))
                        $q->where('seat_number',$request->seating_no);
                    else
                        $q->where('name','LIKE',"%".$request->seating_no."%");
            })->paginate(10);
        });
        dd($results);
    }
	public function scrapper(){
		if(cache('reached')<2000000 || cache('reached')==null)
        for($i=1000000;$i<2000000;$i++){
            $response = \Http::withHeaders([
                'origin' => 'https://natega.youm7.com',
                'referer' => 'https://natega.youm7.com/',
            ])->asForm()->post('https://natega.youm7.com/Home/Natega', [
                'seatNo' => $i,
            ]);
            $data = $this->extract_data_html($response->body());
            if($data['data']==1){
                \App\Models\Result::firstOrCreate([
                    'seat_number'=>$i
                ],[
                    'name'=>$this->optimize_string($data['name']),
                    'seat_number'=>$i,
                    'school'=>$data['school'],
                    'education_admin'=>$data['education_admin'],
                    'student_status'=>$data['student_status'],
                    'education_type'=>$data['education_type'],
                    'section'=>$data['section'],
                    'arabic_language'=>$data['arabic_language'],
                    'first_foreign_language'=>$data['first_foreign_language'],
                    'second_foreign_language'=>$data['second_foreign_language'],
                    'result_of_pure_math'=>$data['result_of_pure_math'],
                    'history'=>$data['history'],
                    'geography'=>$data['geography'],
                    'philosophy'=>$data['philosophy'],
                    'psychology'=>$data['psychology'],
                    'chemistry'=>$data['chemistry'],
                    'biology'=>$data['biology'],
                    'geology'=>$data['geology'],
                    'physics'=>$data['physics'],
                    'applied_mathematics'=>$data['applied_mathematics'],
                    'religious_education'=>$data['religious_education'],
                    'national_education'=>$data['national_education'],
                    'economics_and_statistics'=>$data['economics_and_statistics'],
                    'total_results'=>$data['total_results'],
                    ]
                );
            }
            cache(['reached'=>$i]);
        }
	}

	public function extract_data_html($html){
        $crawler = new Crawler($html);
        $student_count  = $crawler->filter('div.full-result')->count();
        if($student_count==0)
            return [
                'data'=>0
            ];

        $student  = $crawler->filter('div.full-result')->html();
        $detailed_result = $crawler->filter('div.result-info')->html();
        $subjects_in_english=[
            "اللغة العربية :"=>"arabic_language",
            'اللغة الأجنبية الأولى :'=>"first_foreign_language",
            'اللغة الأجنبية الثانية :'=>"second_foreign_language",
            'مجموع الرياضيات البحتة :'=>"result_of_pure_math",
            'التاريخ :'=>"history",
            'الجغرافيا :'=>"geography",
            'الفلسفة والمنطق :'=>"philosophy",
            'علم النفس والاجتماع :'=>"psychology",
            'الكيمياء :'=>"chemistry",
            'الأحياء :'=>"biology",
            'الجيولوجيا وعلوم البيئة :'=>"geology",
            'الرياضيات التطبيقية :'=>"applied_mathematics",
            'الفيزياء :'=>"physics",
            'مجموع الدرجات :'=>"total_results",
            'التربية الدينية :'=>"religious_education",
            'التربية الوطنية :'=>"national_education",
            'الاقتصاد والإحصاء :'=>"economics_and_statistics"
        ];
 
        $results = [
            'data'=>1
        ];

        $userData_english=[
            'الأسم:'=>"name",
            'المدرسة :'=>"school",
            'الأدارة التعليمية :'=>"education_admin",
            'حالة الطالب :'=>"student_status",
            'نوعية التعليم :'=>"education_type",
            'الشعبة :'=>"section"
        ];
        $userData = [];
        $crawler->filter('.full-result .resultItem')->each(function (Crawler $node) use (&$userData,$userData_english) {
            $label = trim($node->filter('.formatt')->text());
            $value = trim($node->filter('span:not(.formatt)')->text());
            if (!empty($label) && !empty($value)) {
                $userData[$userData_english[$label]] = $value;
            }
        }); 
        $results['name']=$userData['name'];
        $results['school']=$userData['school'];
        $results['education_admin']=$userData['education_admin'];
        $results['student_status']=$userData['student_status'];
        $results['education_type']=$userData['education_type'];
        $results['section']=$userData['section'];

        $crawler->filter('.result-details ul li.resultItem')->each(function (Crawler $itemCrawler) use (&$results,$subjects_in_english) {
            $subjectName = $itemCrawler->filter('span.formatt2')->text();
            $subjectGrade = $itemCrawler->filter('span.formatt4')->text();

            $results[$subjects_in_english[$subjectName]] = $subjectGrade;
        });
        return $results;
    }


    public function optimize_string($string){
      $string= str_replace('ة','ه',$string);
      $string= str_replace('عبد ال','عبدال',$string);
      $string= str_replace('ى','ي',$string);
      $string= str_replace('أ','ا',$string);
      $string= str_replace('إ','ا',$string);
      $string= str_replace('آ','ا',$string);
      return $string;
    }


    public function slug($string){
        $t = $string; 
        $specChars = array(
            ' ' => '-',    '!' => '',    '"' => '',
            '#' => '',    '$' => '',    '%' => '',
            '&amp;' => '','&nbsp;' => '', 
            '\'' => '',   '(' => '',
            ')' => '',    '*' => '',    '+' => '',
            ',' => '',    '₹' => '',    '.' => '',
            '/-' => '',    ':' => '',    ';' => '',
            '<' => '',    '=' => '',    '>' => '',
            '?' => '',    '@' => '',    '[' => '',
            '\\' => '',   ']' => '',    '^' => '',
            '_' => '',    '`' => '',    '{' => '',
            '|' => '',    '}' => '',    '~' => '',
            '-----' => '-',    '----' => '-',    '---' => '-',
            '/' => '',    '--' => '-',   '/_' => '-',    
        ); 
        foreach ($specChars as $k => $v) {
            $t = str_replace($k, $v, $t);
        }
 
        return substr($t,0,230);
    }







}