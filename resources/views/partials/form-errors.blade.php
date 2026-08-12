@if($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1">Veuillez corriger les erreurs suivantes :</div>
        <ul class="mb-0">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
