@foreach ($data as $d)
    <span>
        {{ $d->active_devices }}/{{ $d->total_devices }} 
    </span>
@endforeach
