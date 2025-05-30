@extends('layouts.app')

@section('body_class', 'bg-gray') @section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection



@section('content')
<div class="attendance-container">
    <div class="attendance-status">
        @if ($status === '勤務外')
            <p class="status-label">勤務外</p>
        @elseif ($status === '勤務中')
            <p class="status-label">勤務中</p>
        @elseif ($status === '休憩中')
            <p class="status-label">休憩中</p>
        @elseif ($status === '退勤済')
            <p class="status-label">退勤済</p>
        @endif
    </div>

    <h3 class="attendance-date">
        {{ $today }}({{ $dayOfWeek }})
    </h3>

    <h2 class="attendance-time" id="clock">00:00</h2>

    @if ($status === '勤務外')
        <div class="attendance-buttons">
            <form class="attendance-form" action="{{ route('attendance.clockIn') }}" method="post">
                @csrf
                <button class="attendance-btn" type="submit">出勤</button>
            </form>
        </div>
    @elseif ($status === '勤務中')
        <div class="attendance-buttons">
            <form class="attendance-form" action="{{ route('attendance.clockOut') }}" method="post">
                @csrf
                <button class="attendance-btn">退勤</button>
            </form>
            <form class="attendance-form" action="{{ route('attendance.breakStart') }}" method="post">
                @csrf
                <button class="break-btn">休憩入</button>
            </form>
        </div>
    @elseif ($status === '休憩中')
        <div class="attendance-buttons">
            <form class="attendance-form" action="{{ route('attendance.breakEnd') }}" method="post">
                @csrf
                <button class="break-btn">休憩戻</button>
            </form>
        </div>
    @elseif ($status === '退勤済')
        <p class="attendance-message">お疲れ様でした。</p>
    @endif
</div>

<!-- 時刻のリアルタイム更新 -->
<script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('clock').textContent = `${hours}:${minutes}`;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

@endsection