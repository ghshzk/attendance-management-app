@extends('layouts.app')

@section('css') @section('body_class', 'bg-gray')
<link rel="stylesheet" href="{{ asset('css/admin/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list-container">
    <h3 class="list-heading">{{ $date->format('Y年n月j日') }}の勤怠</h3>
    <div class="list-date-nav">
        <a class="date-link" href="{{ route('admin.attendance.index', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}">
            <img class="allow-left-img" src="{{ asset('img/allow.png') }}" alt="矢印">
            <span class="date-link-label">前日</span>
        </a>

        <div class="date-nav-calendar">
            <button class="btn" type="button" onclick="document.getElementById('datePicker').showPicker()">
                <img class="calendar-img" src="{{ asset('img/calendar.png') }}" alt="カレンダー">
            </button>
            <string class="date-btn-label">{{ $date->format('Y/m/d') }}</string>
        </div>

        <form class="form" action="{{ route('admin.attendance.index') }}" method="get">
            <input type="date" name="date" id="datePicker" value="{{ $date->format('Y/m') }}" style="display: none;" onchange="this.form.submit()">
        </form>

        <a class="date-link" href="{{ route('admin.attendance.index', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}">
            <span class="date-link-label">翌日</span>
            <img class="allow-right-img" src="{{ asset('img/allow.png') }}" alt="矢印">
        </a>
    </div>

    <div class="attendance-table">
        <table class="attendance-table__inner">
            <tr class="attendance-table__row">
                <th class="attendance-table__header">名前</th>
                <th class="attendance-table__header">出勤</th>
                <th class="attendance-table__header">退勤</th>
                <th class="attendance-table__header">休憩</th>
                <th class="attendance-table__header">合計</th>
                <th class="attendance-table__header">詳細</th>
            </tr>
            @foreach($attendances as $attendance)
            <tr class="attendance-table__row">
                <td class="attendance-table__data">
                    {{ $attendance->user->name }}
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
</div>
@endsection