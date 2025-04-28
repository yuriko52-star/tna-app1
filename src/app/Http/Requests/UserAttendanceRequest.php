<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => ['nullable','date_format:H:i',],
            'clock_out' => ['nullable', 'date_format:H:i'],
            'breaks.*.clock_in'=> ['nullable' ,'date_format:H:i'] ,
            'breaks.*.clock_out' => ['nullable','date_format:H:i'],
            'reason' => ['required','string'],
        ];
    }
     public function withValidator($validator) 
    {
       $validator->after(function($validator)
       {
        $clockIn = $this->input('clock_in');
        $clockOut = $this->input('clock_out');

        if($clockIn && $clockOut && $clockIn > $clockOut) {
            $validator->errors()->add('clock_time_invalid', '出勤時間もしくは退勤時間が不適切な値です');
        } 
        foreach($this->input('breaks', []) as $index => $break) {
            $breakIn = $break['clock_in'] ?? null;
            $breakOut = $break['clock_out'] ?? null;
            if(($clockIn && $breakIn && $breakIn < $clockIn) || ($clockOut && $breakOut && $breakOut > $clockOut)) {
                $validator->errors()->add("breaks.$index.outside_working_time", '休憩時間が勤務時間外です');
            }
             if($breakIn && $breakOut && $breakIn > $breakOut) {
            $validator->errors()->add("breaks.$index.break_time_invalid", '開始時間もしくは終了時間が不適切な値です');
            } 
        }
       });
    }
     public function messages()
    {
        $messages = [
            'clock_in.date_format' => '出勤時間をスペースを入れずに半角で入力してください（例: 09:00）',
           'clock_out.date_format' => '退勤時間をスペースを入れずに半角で入力してください（例: 18:00）',
            'reason.required' => '備考を記入してください',
           ];

        foreach ($this->input('breaks', []) as $index => $break) {
        
            $messages["breaks.$index.clock_in.date_format"] = "開始時間はスペースを入れずに半角で入力してください（例: 10:00）";
            $messages["breaks.$index.clock_out.date_format"] = "終了時間はスペースを入れずに半角で入力してください(例: 10:30)";
        }
        
        return $messages;
    }

}
