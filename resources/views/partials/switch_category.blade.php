<div class="level">
    <div class="level-left">
        <div class="level-item">
            <a class="button" href="{{ $route }}">
                <span class="icon">
                    <i class="fa fa-chevron-circle-left" aria-hidden="true"></i>
                </span>
                <span>
                    {{ $text }}
                </span>
            </a>
        </div>
        <div class="level-item">
            @include('partials.file_notification')
        </div>
    </div>
</div>