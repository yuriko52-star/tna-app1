<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceRequest extends FormRequest
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
             'clock_in' => ['nullable', 'numeric','date_format:H:i'],
            'clock_out' => ['nullable', 'numeric','date_format:H:i'],
            'breaks.*.clock_in'=> ['nullable', 'numeric','date_format:H:i'] ,
            'breaks.*.clock_out' => ['nullable','numeric','date_format:H:i'],
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
           
        }
       }) ;
    }

    public function messages()
    {
        return [
             'clock_in.date_format' => '出勤時間の形式が正しくありません（例: 09:00）',
             'clock_in.numeric' => '数値で記入してください',
            'clock_out.date_format' => '退勤時間の形式が正しくありません（例: 18:00）',
            'clock_out.numeric' => '数値で記入してください',
            'breaks.*.clock_in.date_format' => '休憩開始時間の形式が正しくありません（例: 12:00）',
            'breaks.*.clock_in.numeric' => '数値で記入してください',
            'breaks.*.clock_out.date_format' => '休憩終了時間の形式が正しくありません（例: 13:00）',
            'breaks.*.clock_out.numeric' => '数値で記入してください',
            'reason.required' => '備考を記入してください',
        ];
    }
}
