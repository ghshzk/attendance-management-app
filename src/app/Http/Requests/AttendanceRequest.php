<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'break_start.*' =>['nullable', 'date_format:H:i'],
            'break_end.*' =>['nullable', 'date_format:H:i'],
            'remark' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.date_format' => '出勤時間は正しい形式で入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間は正しい形式で入力してください',
            'break_start.date_format' => '休憩開始時間は正しい形式で入力してください',
            'break_end.date_format' => '休憩終了時間は正しい形式で入力してください',
            'remark.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breakStart = $this->input('break_start', []);
            $breakEnd = $this->input('break_end', []);

            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                if (!$validator->errors()->has('clock_in') && !$validator->errors()->has('clock_out')) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            foreach ($breakStart as $index => $start) {
                $end = $breakEnd[$index] ?? null;

                //休憩開始があるのに休憩終了がない
                if ($start && !$end) {
                    $validator->errors()->add("break_end.$index", '休憩終了時間を入力してください');
                }
                //休憩終了があるのに休憩開始がない
                if ($end && !$start) {
                    $validator->errors()->add("break_start.$index", '休憩開始時間を入力してください');
                }

                //休憩開始終了ある場合のバリデーション
                if ($start && $end) {
                    if ($start >= $end) {
                        $validator->errors()->add("break_start.$index", '休憩開始時間もしくは休憩終了時間が不適切な値です');
                    }

                    if (($clockIn && $start < $clockIn) || ($clockOut && $start > $clockOut)) {
                        $validator->errors()->add("break_start.$index", '休憩時間が勤務時間外です');
                    }
                    if(($clockIn && $end < $clockIn) || ($clockOut && $end > $clockOut)) {
                        $validator->errors()->add("break_end.$index", '休憩時間が勤務時間外です');
                    }
                }
            }
        });
    }
}
