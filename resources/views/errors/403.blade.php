@php
  $errCode    = 403;
  $errIcon    = '🔒';
  $errTitle   = 'Access Restricted';
  $errMessage = $exception->getMessage() && $exception->getMessage() !== 'This action is unauthorized.'
    ? e($exception->getMessage())
    : 'You don\'t have permission to access this page.<br>Contact your administrator if you think this is a mistake.';
@endphp
@extends('errors.layout')
@section('actions')
  <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}" class="btn btn-secondary">← Go Back</a>
  <a href="{{ route('bms.dashboard') }}" class="btn btn-primary">Dashboard</a>
@endsection
