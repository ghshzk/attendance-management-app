@extends('layouts.app')

@section('css') @section('body_class', 'bg-gray')
<link rel="stylesheet" href="{{ asset('css/correction-request.css') }}">
@endsection

@section('content')
<div class="correction-list-container">
    <h3 class="list-heading">申請一覧</h3>

    <div class="tab">
        <ul class="tab-nav">
            <li class="nav-item">
                <a class="nav-link {{ $status == 'pending' ? 'active' : '' }}" href="{{ route('correction.index', ['status' => 'pending']) }}">
                    承認待ち
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'approved' ? 'active' : '' }}" href="{{ route('correction.index', ['status' => 'approved']) }}">
                    承認済み
                </a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        @if ($requests->count())
        <table class="correction-table">
            <tr class="correction-table__row">
                <th class="correction-table__header">状態</th>
                <th class="correction-table__header">名前</th>
                <th class="correction-table__header">対象日時</th>
                <th class="correction-table__header">申請理由</th>
                <th class="correction-table__header">申請日時</th>
                <th class="correction-table__header">詳細</th>
            </tr>
            @foreach($requests as $request)
            <tr class="correction-table__row">
                <td class="correction-table__data">
                    @if($request->status === 'pending')
                    <span>承認待ち</span>
                    @else
                    <span>承認済み</span>
                    @endif
                </td>
                <td class="correction-table__data">
                    {{ $request->user->name }}
                </td>
                <td class="correction-table__data date">
                    {{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}
                </td>
                <td class="correction-table__data">
                    {{ $request->remark}}
                </td>
                <td class="correction-table__data date">
                    {{ $request->created_at->format('Y/m/d') }}
                </td>
                <td class="correction-table__data">
                    <a class="correction-table__btn" href="{{ route('attendance.show', ['id' => $request->attendance_id]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
        @else
        <p class="message">申請がありません</p>
        @endif
    </div>
</div>

@endsection