<div class="stats">
  <div class="stat"><span class="stat-n">{{ $waiting->count() }}</span><span class="stat-l">Waiting</span></div>
  <div class="stat"><span class="stat-n">{{ $called->count() }}</span><span class="stat-l">Being served</span></div>
  <div class="stat"><span class="stat-n">{{ $doneCount }}</span><span class="stat-l">Done today</span></div>
  <div class="stat"><span class="stat-n">{{ $avgWait !== null ? $avgWait.' min' : '-' }}</span><span class="stat-l">Average wait</span></div>
</div>

<div class="toolbar">
  <form method="POST" action="{{ route('staff.queue.next') }}">
    @csrf
    <button class="btn btn-amber" @disabled($waiting->isEmpty())>Call next patient</button>
  </form>
  <a class="btn btn-ghost" href="{{ route('staff.create') }}">Add patient without phone</a>
</div>

<h2 class="h2">Now serving</h2>
@if($called->isEmpty())
  <p class="empty">Nobody has been called yet.</p>
@else
  <div class="table-wrap"><table>
    <thead><tr><th>No.</th><th>Name</th><th>Priority</th><th>Called</th><th></th></tr></thead>
    <tbody>
    @foreach($called as $t)
      <tr class="p-{{ $t->effective_priority }}">
        <td class="n">{{ $t->number }}</td>
        <td>{{ $t->patient_name ?: 'No name given' }}</td>
        <td><span class="badge p-{{ $t->effective_priority }}">{{ $t->priority_label }}</span></td>
        <td>{{ $t->called_at->diffForHumans() }}</td>
        <td><div class="actions">
          <a class="btn btn-ghost btn-sm" href="{{ route('staff.ticket', $t) }}">View</a>
          @include('staff.partials.status-button', ['t' => $t, 'to' => 'done', 'label' => 'Done', 'class' => 'btn-primary'])
          @include('staff.partials.status-button', ['t' => $t, 'to' => 'no_show', 'label' => 'No show', 'class' => 'btn-ghost'])
          @include('staff.partials.status-button', ['t' => $t, 'to' => 'waiting', 'label' => 'Back to queue', 'class' => 'btn-ghost'])
        </div></td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
@endif

<h2 class="h2">Waiting</h2>
@if($waiting->isEmpty())
  <p class="empty">The queue is empty. New tickets appear here when patients scan the entrance QR.</p>
@else
  <div class="table-wrap"><table>
    <thead><tr><th>No.</th><th>Name</th><th>Age</th><th>Priority</th><th>Main complaint</th><th>Waiting</th><th></th></tr></thead>
    <tbody>
    @foreach($waiting as $t)
      <tr class="p-{{ $t->effective_priority }}">
        <td class="n">{{ $t->number }}</td>
        <td>{{ $t->patient_name ?: 'No name given' }}</td>
        <td>{{ $t->age_label }}</td>
        <td>
          <span class="badge p-{{ $t->effective_priority }}">{{ $t->priority_label }}</span>
          @if($t->answers === null)<span class="hint">form not filled</span>
          @elseif(! $t->is_confirmed)<span class="hint">to confirm</span>@endif
        </td>
        <td>{{ $t->symptom_summary }}</td>
        <td>{{ $t->created_at->diffForHumans(null, true) }}</td>
        <td><div class="actions">
          <a class="btn btn-ghost btn-sm" href="{{ route('staff.ticket', $t) }}">View</a>
          @include('staff.partials.status-button', ['t' => $t, 'to' => 'called', 'label' => 'Call', 'class' => 'btn-primary'])
        </div></td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
@endif
