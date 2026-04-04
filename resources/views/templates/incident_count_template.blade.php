@foreach ($data as $d)
    <span class="{{$d->incident_count > 0 ? 'text-danger' : 'text-success'}}", style="font-size: 200%">
        {{ $d->incident_count }}
    </span>
@endforeach
