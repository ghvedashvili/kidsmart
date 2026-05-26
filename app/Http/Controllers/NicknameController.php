<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NicknameController extends Controller
{
private function getRules(string $nickname): array
{
    $nicknameUpper = strtoupper($nickname);
    $numbers = [];
    preg_match_all('/\d/', $nickname, $numbers);

    $days = [
        "SUN" => ["SUNDAY", "კვირა", "კვი"],
        "MON" => ["MONDAY", "ორშაბათი", "ორშ"],
        "TUE" => ["TUESDAY", "სამშაბათი", "სამ"],
        "WED" => ["WEDNESDAY", "ოთხშაბათი", "ოთხ"],
        "THU" => ["THURSDAY", "ხუთშაბათი", "ხუთ"],
        "FRI" => ["FRIDAY", "პარასკევი", "პარ"],
        "SAT" => ["SATURDAY", "შაბათი", "შაბ"]
    ];
    $months = [
        ["january","jan","იანვარი","იან"],
        ["february","feb","თებერვალი","თებ"],
        ["march","mar","მარტი","მარ"],
        ["april","apr","აპრილი","აპრ"],
        ["may","may","მაისი","მაი"],
        ["june","jun","ივნისი","ივნ"],
        ["july","jul","ივლისი","ივლ"],
        ["august","aug","აგვისტო","აგვ"],
        ["september","sep","სექტემბერი","სექ"],
        ["october","oct","ოქტომბერი","ოქტ"],
        ["november","nov","ნოემბერი","ნოე"],
        ["december","dec","დეკემბერი","დეკ"]
    ];
    $nuclearCodes = ["US","RU","GB","FR","CN","IN","PK","KP","IL"];
    $primes = ["11","13","17","19","23","29","31","37","41","43","47","53","59","61","67","71","73","79","83","89","97"];

    return [
        
        
        ['id'=>1,'text'=>'Nickname უნდა შეიცავდეს მინიმუმ 5 სიმბოლოს','passed'=>mb_strlen($nickname)>=5],
        ['id'=>2,'text'=>'Nickname უნდა შეიცავდეს ციფრს','passed'=>preg_match('/\d/',$nickname)],
        ['id'=>3,'text'=>'Nickname უნდა შეიცავდეს დიდ ლათინურ ასოს','passed'=>preg_match('/[A-Z]/',$nickname)],
        ['id'=>4,'text'=>'Nickname უნდა შეიცავდეს სპეციალურ სიმბოლოს','passed'=>preg_match('/[!@#$%^&*()_\-+=\[\]{};:"\\|,.<>\/?]/',$nickname)],
        ['id'=>5,'text'=>'Nickname-ში ციფრების ჯამი უნდა იყოს 15','passed'=>array_sum(array_map('intval',$numbers[0]??[]))===15],
        ['id'=>6,'text'=>'Nickname უნდა შეიცავდეს თვეს და კვირის რომელიმე დღეს','passed'=>(function() use($nickname,$nicknameUpper,$days,$months){
            $allDays = array_merge(...array_values($days));
            $allDays = array_merge(array_keys($days), $allDays);
            $allMonths = array_merge(...$months);
            $hasDay   = collect($allDays)->contains(fn($d)=>str_contains($nicknameUpper, strtoupper($d)));
            $hasMonth = collect($allMonths)->contains(fn($m)=>str_contains(strtolower($nickname), strtolower($m)));
            return $hasDay && $hasMonth;
        })()],
        ['id'=>7,'text'=>'Nickname უნდა შეიცავდეს ჭადრაკის ნოტაციას','passed'=>preg_match('/([a-h][1-8]|[nbrqk][a-h][1-8])/i',$nickname)],
        ['id'=>9,'text'=>'Nickname უნდა შეიცავდეს ემოჯის','passed'=>preg_match('/[\x{1F300}-\x{1F9FF}]/u',$nickname)],
        ['id'=>10,'text'=>'Nickname უნდა შეიცავდეს რომელიმე ბირთვული სახელმწიფოს ISO კოდს','passed'=>collect($nuclearCodes)->contains(fn($c)=>str_contains($nicknameUpper,$c))],
        ['id'=>11,'text'=>'Nickname უნდა შეიცავდეს რომაულ ციფრს (მინიმუმ 3 სიმბოლო)','passed'=>preg_match('/[IVXLCDM]{3,}/i',$nickname)],
        ['id'=>13,'text'=>'Nickname-ში ყოველი მე-4 ასო უნდა იყოს დიდი','passed'=>function() use($nickname){
            $letters = preg_replace('/[^a-zA-Z]/','',$nickname);
            if(strlen($letters)<4) return false;
            for($i=3;$i<strlen($letters);$i+=4){
                if($letters[$i]!==strtoupper($letters[$i])) return false;
            }
            return true;
        }],
        ['id'=>14,'text'=>'Nickname უნდა შეიცავდეს 3 ხმოვანს ზედიზედ','passed'=>preg_match('/[aeiouAEIOU]{3,}/',$nickname)],
        ['id'=>15,'text'=>'Nickname უნდა შეიცავდეს ათწილადს','passed'=>preg_match('/\d+\.\d+/',$nickname)],
        ['id'=>16,'text'=>'Nickname არ უნდა შეიცავდეს ქართულ ასოებს','passed'=>!preg_match('/[\x{10D0}-\x{10FF}]/u',$nickname)],
        ['id'=>17,'text'=>'Nickname არ უნდა შეიცავდეს მიმდევრობით ერთსა და იმავე სიმბოლოს 2-ზე მეტჯერ','passed'=>!preg_match('/(.)\1\1/',$nickname)],
        ['id'=>18,'text' => 'Nickname უნდა შეიცავდეს ტემპერატურას (-375°C-დან 10000°C-მდე ან °F)',
            'passed' => preg_match('/(-?\d{1,5})\s*(?:°|º|deg)\s*([CF])/iu', $nickname, $matches)
                && (
                    (strtoupper($matches[2]) === 'C' && $matches[1] >= -375 && $matches[1] <= 10000) ||
                    (strtoupper($matches[2]) === 'F')
                )
        ],
        ['id'=>12,'text'=>'Nickname-ის სიგრძე უნდა იყოს მაქსიმუმ 35 სიმბოლო','passed'=>mb_strlen($nickname)<=35],
        ['id'=>19,'text'=>'Nickname უნდა შეიცავდეს ძვირფასი ლითონის ქიმიურ სიმბოლოს','passed'=>(function() use($nickname){
            $precious = ['Au','Ag','Pt','Pd','Rh','Ir','Ru','Os','Re'];
            return collect($precious)->contains(fn($s)=>str_contains($nickname,$s));
        })()],
        ['id'=>20,'text'=>'Nickname უნდა შეიცავდეს მსუბუქი ავტომობილების სანომრე ნიშანს (ქართული სტანადარტით) ','passed'=>preg_match('/[A-Z]{2}-\d{3}-[A-Z]{2}/',$nicknameUpper)],
        ['id'=>21,'text'=>'Nickname-ში არ უნდა იყოს "41"','passed'=>!str_contains($nickname,'41')],
        ['id'=>22,'text'=>'Nickname უნდა შეიცავდეს მარტივ 2 ნიშნა რიცხვს','passed'=>collect($primes)->contains(fn($p)=>str_contains($nickname,$p))],
        ['id'=>23,'text'=>'Nickname უნდა შეიცავდეს ციფრულ დროს (12:34, 23:59)','passed'=>preg_match('/([01]?[0-9]|2[0-3]):[0-5][0-9]/',$nickname)],
        ['id'=>27,'text'=>'Nickname-ში ყველა ლათინური სიმბოლო გამოყენებული უნდა იყოს მხოლოდ ერთხელ','passed'=>(function() use($nickname){
            $letters = strtolower(preg_replace('/[^a-zA-Z]/', '', $nickname));
            return $letters === '' || strlen($letters) === count(array_unique(str_split($letters)));
        })()],
        ['id'=>24,'text'=>'Nickname-ში აკრძალულია კოდი RU (რუსეთი ოკუპანტია!)','passed'=>!str_contains($nicknameUpper,'RU')],
        ['id'=>25,'text'=>'Nickname კენტი და ლუწი ციფრები უნდა იყოს თანაბარი რაოდენობის','passed'=>function() use($nickname){
            $numbers = preg_match_all('/\d/',$nickname,$matches) ? $matches[0] : [];
            $even = count(array_filter($numbers, fn($n)=>intval($n)%2===0));
            $odd  = count(array_filter($numbers, fn($n)=>intval($n)%2!==0));
            return $even === $odd;
        }],
        ['id'=>28,'text'=>'Nickname უნდა შეიცავდეს პოკერის სტრიტის ნოტაციას','passed'=>(function() use($nickname){
            $upper = strtoupper($nickname);
            $straights = ['A2345','23456','34567','45678','56789','6789T','789TJ','89TJQ','9TJQK','TJQKA'];
            foreach($straights as $s){
                if(str_contains($upper,$s)) return true;
                if(str_contains($upper,strrev($s))) return true;
            }
            return false;
        })()],
        ['id'=>26,'text'=>'Nickname-ში ციფრების ნამრავლი 0-ზე მეტი უნდა იყოს','passed'=>function() use($nickname){
            $numbers = preg_match_all('/\d/',$nickname,$matches) ? $matches[0] : [];
            if(empty($numbers)) return false;
            $product = array_product(array_map('intval',$numbers));
            return $product > 0;
        }],
        ['id'=>999,'text'=>'Nickname უნდა შეიცავდეს ქაფთჩას: "cdm1S"','passed'=>str_contains($nickname,'cdm1S')]
        
    ];
}




    private function normalizeRules(array $rules): array
    {
        return array_map(fn($r)=>[
            'id'=>$r['id'],
            'text'=>$r['text'],
            'passed'=>is_callable($r['passed'])?(bool)$r['passed'](): (bool)$r['passed']
        ], $rules);
    }

    public function live(Request $request, $level)
    {
        if (!$request->expectsJson()) return response()->json(['locked'=>true],400);

        $user=Auth::user();
        if(!$user || $user->level!=$level) return response()->json(['locked'=>true]);

        return response()->json([
            'locked'=>false,
            'rules'=>$this->normalizeRules($this->getRules($request->nickname??''))
        ]);
    }

    public function submit(Request $request,$level)
    {
        if(!$request->expectsJson()) return response()->json(['status'=>'error'],400);

        $user=Auth::user();
        if(!$user || $user->level!=$level) return response()->json(['status'=>'locked']);

        $nickname=$request->nickname??'';
        $rules=$this->normalizeRules($this->getRules($nickname));

        if(!collect($rules)->every(fn($r)=>$r['passed'])){
            return response()->json(['status'=>'error','rules'=>$rules]);
        }

        try{
            $user->nickname=$nickname;
            $user->level+=1;
            $user->save();
        }catch(\Throwable $e){
            return response()->json(['status'=>'error'],500);
        }

        return response()->json(['status'=>'success','nickname'=>$nickname,'newLevel'=>$user->level]);
    }
}
