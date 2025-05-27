@extends('layouts.app')

@section('css')
@section('body_class', 'bg-gray')
<link rel="stylesheet" href="{{ asset('css/admin/staff-list.css') }}">
@endsection

@section('content')
<div class="staff-list-container">
    <h3 class="list-heading">スタッフ一覧</h3>
    <div class="staff-table">
        <table class="staff-table__inner">
            <tr class="staff-table__row">
                <th class="staff-table__header">名前</th>
                <th class="staff-table__header">メールアドレス</th>
                <th class="staff-table__header">月次勤怠</th>
            </tr>
            @foreach($staffs as $staff)
            <tr class="staff-table__row">
                <td class="staff-table__data">
                    {{ $staff->name }}
                </td>
                <td class="staff-table__data">
                    {{ $staff->email }}
                </td>
                <td class="staff-table__data">
                    <a class="staff-table__btn" href="{{ route('admin.staff.attendance', ['id' => $staff->id]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>

@endsection