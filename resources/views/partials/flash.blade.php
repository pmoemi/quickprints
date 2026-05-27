@if(session('success'))
    <div class="flash flash-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="flash flash-error">
        <ul style="margin:0; padding-left:1.25rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
