@foreach ($data as $d)
    <span style="font-size: 200%">
        {{ floor($d->avg * 100) }}%
    </span>
@endforeach
