@extends('layouts.app')

@section('contents')

@include('partials.switch_category', ['text' => 'Files', 'route' => route('file.index')])

<form method="POST" action="{{ route('torrent.copy') }}">
{{ csrf_field() }}
<h3 class="title is-3">
    Torrents
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

@foreach ($torrents as $torrent)
    <div class="columns">
        <div class="column">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="copies[{{ $torrent->id }}]" value="{{ $torrent->id }}">
                    {{ $torrent->webFriendlyName() }} ({{ $torrent->formattedSize() }})
                    @if ($torrent->isStillDownloading())
                        ETA: {{ $torrent->formattedEta() }}
                        Done: {{ $torrent->percent }}%
                        <a href="{{ route('torrent.update', $torrent->torrent_id) }}" class="button is-small is-light">
                            <span class="icon">
                                <i class="fa fa-refresh"></i>
                            </span>
                        </a>
                    @endif
                    @if ($torrent->was_copied)
                        <span class="icon" title="Already copied">
                            <i class="fa fa-check"></i>
                        </span>
                    @endif
                </label>
            </div>
        </div>
    </div>
@endforeach

</form>

@endsection
