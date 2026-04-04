@foreach ($data as $d)
    <span style="font-size: 150%">
        {{ $d->working }}/{{ $d->total }}
    </span>
@endforeach
