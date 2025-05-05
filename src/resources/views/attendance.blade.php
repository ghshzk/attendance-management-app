@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="attendance-status">
        @if ($status === 'pending')
            <p class="status_label">勤務外</p>
        @elseif ($status === 'working')
            <p class="status_label">出勤中</p>
        @elseif ($status === 'break')
            <p class="status_label">休憩中</p>
        @elseif ($status === 'clocked_out')
            <p class="status_label">退勤済</p>
        @endif
    </div>

    <h3 class="attendance-date">
        {{ $today }} ({{ $dayOfWeek }})
    </h3>

    <h2 class="attendance-time" id="clock">00:00</h2>

    @if ($status === 'pending')
        <form class="attendance-form" action="" method="post">
            @csrf
            <button class="attendance-btn" type="submit">出勤</button>
        </form>
    @elseif ($status === 'working')
        <form class="attendance-form" action="" method="post">
            @csrf
            <button class="attendance-btn">退勤</button>
        </form>
        <form class="attendance-form" action="" method="post">
            @csrf
            <button class="break-btn">休憩入</button>
        </form>
    @elseif ($status === 'break')
        <form class="attendance-form" action="" method="post">
            @csrf
            <button class="break-btn">休憩戻</button>
        </form>
    @elseif ($status === 'clocked_out')
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