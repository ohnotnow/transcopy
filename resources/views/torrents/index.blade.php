@extends('layouts.app')

@section('contents')

<form method="POST" action="{{ route('torrent.copy') }}">
{{ csrf_field() }}
<h3 class="text-xl shadow rounded p-4 bg-grey-lighter">
    <div class="inline-flex items-center">
        <div class="flex-1 mx-2">
            <button title="Download">
                @svg('zondicons/arrow-outline-down.svg', 'icon-button')
            </button>
        </div>
        <div class="flex-1 mx-2">
            <a href="{{ route('torrent.refresh') }}" title="Refresh list" onClick="spin()">
                @svg('zondicons/reload.svg', 'icon-button refresh-button')
            </a>
        </div>
        <div class="flex-1 mx-2">
            <span class="underline">Torrents</span>
        </div>
        <div class="flex-1">
            <a href="{{ route('file.index') }}">Files</a>
        </div>
    </div>
    @include('partials.file_notification')
</h3>

<div class="py-8 px-4 border-l-2 border-grey">
    <torrent-list></torrent-list>
@foreach ($torrents as $torrent)
    <div class="mb-4">
        <label>
            <input type="checkbox" name="copies[{{ $torrent->id }}]" value="{{ $torrent->id }}">
            {{ $torrent->webFriendlyName() }} 
            <span class="opacity-50">
                ({{ $torrent->formattedSize() }})
                @if ($torrent->isStillDownloading())
                    ETA: {{ $torrent->formattedEta() }}
                    Done: {{ $torrent->percent }}%
                    <a href="{{ route('torrent.update', $torrent->torrent_id) }}" class="icon-small">
                        <span class="w-4">
                            @svg('zondicons/reload.svg', 'icon-small')
                        </span>
                    </a>
                @endif
                @if ($torrent->wasAlreadyCopied())
                    <span title="Already copied">
                        @svg('zondicons/checkmark.svg', 'w-4')
                    </span>
                @endif
            </span>
        </label>
    </div>
@endforeach
</div>
</form>

@endsection
