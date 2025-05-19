@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/correction-request.css') }}">
@endsection

@section('content')
<div class="attendance-list-container">
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
        <table class="attendance-table">
            <tr class="attendance-table__row">
                <th class="attendance-table__header">状態</th>
                <th class="attendance-table__header">名前</th>
                <th class="attendance-table__header">対象日時</th>
                <th class="attendance-table__header">申請理由</th>
                <th class="attendance-table__header">申請日時</th>
                <th class="attendance-table__header">詳細</th>
            </tr>
            @foreach($requests as $request)
            <tr>
                <td class="attendance-table__data">
                    @if($request->status === 'pending')
                    <span>承認待ち</span>
                    @else
                    <span>承認済み</span>
                    @endif
                </td>
                <td class="attendance-table__data">
                    {{ $request->user->name }}
                </td>
                <td class="attendance-table__data">
                    {{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}
                </td>
                <td class="attendance-table__data">
                    {{ $request->remark}}
                </td>
                <td class="attendance-table__data">
                    {{ $request->created_at->format('Y/m/d') }}
                </td>
                <td class="attendance-table__data">
                    <a class="attendance-table__btn" href="{{ route('attendance.show', ['id' => $request->id]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
        @else
        <p>申請がありません</p>
        @endif
    </div>
</div>

@endsection