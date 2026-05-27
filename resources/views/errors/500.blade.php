@php
  $errCode    = 500;
  $errIcon    = '⚙️';
  $errTitle   = 'Something Went Wrong';
  $errMessage = 'An unexpected error occurred on the server.<br>Please try again — if the problem persists, contact support.';
@endphp
@extends('errors.layout')
@section('actions')
  <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}" class="btn btn-secondary">← Go Back</a>
  <a href="{{ route('bms.dashboard') }}" class="btn btn-primary">Dashboard</a>
@endsection
