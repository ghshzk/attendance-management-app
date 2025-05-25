@extends('layouts.app')

@section('css') @section('body_class', 'bg-gray')
<link rel="stylesheet" href="{{ asset('css/attendance-show.css') }}">
@endsection

@section('content')
<div class="detail-container">
    <h3 class="detail-heading">勤怠詳細</h3>
    <form class="detail-form" action="{{ route('attendance.update',['id' => $attendance->id]) }}" method="post">
        @csrf
        <div class="detail-table">
            <table class="detail-table__inner">
                <tr class="detail-table__row">
                    <th class="detail-table__header">名前</th>
                    <td class="detail-table__data">
                        <span class="name">
                            {{ $attendance->user->name }}</td>
                        </span>
                </tr>

                <tr class="detail-table__row">
                    <th class="detail-table__header">日付</th>
                    <td class="detail-table__data">
                        <span class="year">
                            {{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}
                        </span>
                        <span class="month-day">
                            {{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}
                        </span>
                    </td>
                </tr>

                <tr class="detail-table__row">
                    <th class="detail-table__header">出勤・退勤</th>
                    <td class="detail-table__data">
                        @if($pendingCorrection)
                            <span class="clock-in">
                                {{ $pendingCorrection->clock_in ? \Carbon\Carbon::parse($pendingCorrection->clock_in)->format('H:i') : '' }}
                            </span>
                            〜
                            <span class="clock-out">
                                {{ $pendingCorrection->clock_out ? \Carbon\Carbon::parse($pendingCorrection->clock_out)->format('H:i') : '' }}
                            </span>
                        @else
                            <input class="detail-table__input input-clock-in" type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                            〜
                            <input class="detail-table__input input-clock-out" type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                            <div class="error-message">
                                @error('clock_in')
                                {{ $message }}
                                @enderror

                                @error('clock_out')
                                {{ $message }}
                                @enderror
                            </div>
                        @endif
                    </td>
                </tr>

                @if($pendingCorrection)
                    @foreach($pendingCorrection->correctionBreakTimes as $index => $break)
                        <tr class="detail-table__row">
                            <th class="detail-table__header">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                            <td class="detail-table__data">
                                <span class="break-start">
                                    {{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '-' }}
                                </span>
                                〜
                                <span class="break-end">
                                    {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '-' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @else
                    @foreach($attendance->breakTimes as $index => $break)
                        <tr class="detail-table__row">
                            <th class="detail-table__header">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                            <td class="detail-table__data">
                                <input class="detail-table__input input-break-start" type="time" name="break_start[{{ $index }}]" value="{{ old('break_start.' . $index, $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                                〜
                                <input class="detail-table__input input-break-end" type="time" name="break_end[{{ $index }}]" value="{{ old('break_end.' . $index, $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                                <div class="error-message">
                                    @error("break_start.$index")
                                    {{ $message }}
                                    @enderror

                                    @error("break_end.$index")
                                    {{ $message }}
                                    @enderror
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif

                @php
                    $nextIndex = count($attendance->breakTimes);
                @endphp

                @if(!$pendingCorrection) <!-- 修正申請中じゃない場合は表示 -->
                    <tr class="detail-table__row">
                        <th class="detail-table__header">休憩{{ count($attendance->breakTimes) + 1 }}</th>
                        <td class="detail-table__data">
                            <input class="detail-table__input input-break-start" type="time" name="break_start[{{ $nextIndex }}]" value="{{ old('break_start.' . $nextIndex) }}">
                            〜
                            <input class="detail-table__input input-break-end" type="time" name="break_end[{{ $nextIndex }}]" value="{{ old('break_end.' . $nextIndex) }}">
                            <div class="error-message">
                                @error("break_start.$nextIndex")
                                {{ $message }}
                                @enderror

                                @error("break_end.$nextIndex")
                                {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                @endif

                <tr class="detail-table__row">
                    <th class="detail-table__header">備考</th>
                    <td class="detail-table__data">
                        @if($pendingCorrection)
                            <span class="remark">
                                {{ $pendingCorrection->remark }}
                            </span>
                        @else
                            <textarea class="detail-table__textarea" name="remark">{{ old('remark', $attendance->remark) }}</textarea>
                            <div class="error-message">
                                @error('remark')
                                {{ $message }}
                                @enderror
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        @if($pendingCorrection)
            <p class="detail-form__notice">*承認待ちのため修正はできません。</p>
        @else
            <button class="detail-form__btn" type="submit">修正</button>
        @endif
    </form>
</div>
@endsection