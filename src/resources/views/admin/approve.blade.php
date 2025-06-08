@extends('layouts.app')

@section('css') @section('body_class', 'bg-gray')
<link rel="stylesheet" href="{{ asset('css/admin/approve.css') }}">
@endsection

@section('content')
<div class="approve-container">
    <h3 class="approve-heading">勤怠詳細</h3>
    <form class="approve-form" action="{{ route('admin.approve.update', ['attendance_correct_request' => $pendingCorrection->id]) }}" method="post">
        @csrf
        @method('PUT')
        <div class="approve-table">
            <table class="approve-table__inner">
                <tr class="approve-table__row">
                    <th class="approve-table__header">名前</th>
                    <td class="approve-table__data">
                        <span class="name">
                            {{ $attendance->user->name }}
                        </span>
                    </td>
                </tr>

                <tr class="approve-table__row">
                    <th class="approve-table__header">日付</th>
                    <td class="approve-table__data">
                        <span class="year">
                            {{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}
                        </span>
                        <span class="month-day">
                            {{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}
                        </span>
                    </td>
                </tr>

                <tr class="approve-table__row">
                    <th class="approve-table__header">出勤・退勤</th>
                    <td class="approve-table__data">
                        <span class="clock-in">
                            {{ $pendingCorrection->clock_in ? \Carbon\Carbon::parse($pendingCorrection->clock_in)->format('H:i') : '' }}
                        </span>
                        〜
                        <span class="clock-out">
                            {{ $pendingCorrection->clock_out ? \Carbon\Carbon::parse($pendingCorrection->clock_out)->format('H:i') : '' }}
                        </span>
                    </td>
                </tr>

                @foreach($pendingCorrection->correctionBreakTimes as $index => $break)
                    <tr class="approve-table__row">
                        <th class="approve-table__header">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                        <td class="approve-table__data">
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

                <tr class="approve-table__row">
                    <th class="approve-table__header">備考</th>
                    <td class="approve-table__data">
                        <span class="remark">
                            {{ $pendingCorrection->remark }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        @if($pendingCorrection->status === 'approved')
            <p class="approve-form__completed">承認済み</p>
        @else
            <button class="approve-form__btn" type="submit">承認</button>
        @endif
    </form>
</div>
@endsection