@foreach($data as $d)
 {{ floor($d->avg * 100) }}%
@endforeach