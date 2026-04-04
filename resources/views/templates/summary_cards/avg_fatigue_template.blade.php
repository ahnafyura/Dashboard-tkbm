@foreach ($data as $d)
    <span style="font-size: 150%">
        {{ floor($d->avg * 100) }}%
    </span>
@endforeach
