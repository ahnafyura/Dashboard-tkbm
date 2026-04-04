@foreach ($data as $d)
    <span style="font-size: 200%">
        {{ $d->working }}/{{ $d->total }}
    </span>
@endforeach
