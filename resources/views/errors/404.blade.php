@php
  $errCode    = 404;
  $errIcon    = '🔍';
  $errTitle   = 'Page Not Found';
  $errMessage = 'The page you\'re looking for doesn\'t exist or may have been moved.<br>Double-check the URL or head back to the dashboard.';
@endphp
@extends('errors.layout')
@section('actions')
  <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}" class="btn btn-secondary">← Go Back</a>
  <a href="{{ route('bms.dashboard') }}" class="btn btn-primary">Dashboard</a>
@endsection
