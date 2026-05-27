@php
  $errCode    = 419;
  $errIcon    = '⏱';
  $errTitle   = 'Session Expired';
  $errMessage = 'Your session has timed out for security reasons.<br>Please refresh the page and try again.';
@endphp
@extends('errors.layout')
@section('actions')
  <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}" class="btn btn-secondary">← Refresh</a>
  <a href="{{ route('bms.login') }}" class="btn btn-primary">Sign In Again</a>
@endsection
