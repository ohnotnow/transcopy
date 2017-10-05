@if ($failedJobs->count() > 0)
<article class="message is-danger">
  <div class="message-header">
    <p>The following jobs failed</p>
    <button class="delete" aria-label="delete"></button>
  </div>
  <div class="message-body">
    @foreach ($failedJobs as $job)
        <li>{{ $job->id }}</li>
    @endforeach
  </div>
</article>
@endif