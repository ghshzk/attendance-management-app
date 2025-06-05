@extends('layouts.app')

@section('css')
@section('body_class', 'bg-gray')
<link rel="stylesheet" href="{{ asset('css/admin/staff-attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list-container">
    <h3 class="list-heading">{{ $staff->name }}さんの勤怠</h3>
    <div class="list-month-nav">
        <a class="month-link" href="{{ route('admin.staff.attendance', ['id' => $staff->id, 'month' => $month->copy()->subMonth()->format('Y-m-01')]) }}">
            <img class="allow-left-img" src="{{ asset('img/allow.png') }}" alt="矢印">
            <span class="month-link-label">前月</span>
        </a>

        <div class="month-nav-calendar">
            <button class="btn" type="button" onclick="document.getElementById('monthPicker').showPicker()">
                <img class="calendar-img" src="{{ asset('img/calendar.png') }}" alt="カレンダー">
            </button>
            <string class="month-btn-label">{{ $month->format('Y/m') }}</string>
        </div>

        <form class="form" action="{{ route('admin.staff.attendance', ['id' => $staff->id]) }}" method="get">
            <input type="month" name="month" id="monthPicker" value="{{ $month->format('Y/m') }}" style="display: none;" onchange="this.form.submit()">
        </form>

        <a class="month-link" href="{{ route('attendance.index', ['id' => $staff->id, 'month' => $month->copy()->addMonth()->format('Y-m-01')]) }}">
            <span class="month-link-label">翌月</span>
            <img class="allow-right-img" src="{{ asset('img/allow.png') }}" alt="矢印">
        </a>
    </div>

    <div class="attendance-table">
        <table class="attendance-table__inner">
            <tr class="attendance-table__row">
                <th class="attendance-table__header">日付</th>
                <th class="attendance-table__header">出勤</th>
                <th class="attendance-table__header">退勤</th>
                <th class="attendance-table__header">休憩</th>
                <th class="attendance-table__header">合計</th>
                <th class="attendance-table__header">詳細</th>
            </tr>
            @foreach($attendances as $attendance)
            <tr class="attendance-table__row">
                <td class="attendance-table__data">
                    {{ \Carbon\Carbon::parse($attendance->date)->format('m/d') }}({{ ['日','月','火','水','木','金','土'][\Carbon\Carbon::parse($attendance->date)->dayOfWeek] }})
                </td>
                <td class="attendance-table__data">
                    {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                </td>
                <td class="attendance-table__data">
                    {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                </td>
                <td class="attendance-table__data">{{ $attendance->break_time ?? '-' }}</td>
                <td class="attendance-table__data">{{ $attendance->work_time }}</td>
                <td class="attendance-table__data">
                    <a class="attendance-table__btn" href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    <a class="csv-export-btn" href="{{ route('admin.staff.attendance.csv', ['id' => $staff->id, 'month' => $month->format('Y-m-01')]) }}">
        CSV出力
    </a>
</div>

@endsection
