@extends('layouts.app')

@section('contents')

@include('partials.switch_category', ['text' => 'Files', 'route' => route('file.index')])

<form method="POST" action="{{ route('torrent.copy') }}">
{{ csrf_field() }}
<h3 class="title is-3">
    Current Torrents
    <button class="button is-success">
        <span class="icon">
            <i class="fa fa-download"></i>
        </span>
    </button>
    <a href="{{ route('torrent.refresh') }}" class="button is-warning">
        <span class="icon">
            <i class="fa fa-refresh"></i>
        </span>
    </a>
</h3>


<table class="table is-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>ID</th>
            <th>Size</th>
            <th>ETA</th>
            <th>%</th>
            <th>Copy?</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($torrents as $torrent)
            <tr>
                <td>
                    {{ $torrent->name}}
                </td>
                <td>
                    {{ $torrent->torrent_id }}
                </td>
                <td>{{ $torrent->formattedSize() }}</td>
                <td>{{ $torrent->formattedEta() }}</td>
                <td>{{ $torrent->formattedPercentDone() }}</td>
                <td>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="copies[{{ $torrent->id }}]" value="{{ $torrent->id }}">
                        </label>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</form>

@endsection
