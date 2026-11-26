<div wire:poll.60000ms="refresh" class="dashboard-wrapper">
    @include('livewire.partials.public-header')
    @include('livewire.partials.public-filters')
    @include('livewire.partials.public-jadwal-table')
    @if($showPublicKalender)
        @include('livewire.partials.public-kalender-gantt')
    @endif
    @include('livewire.partials.public-detail-modal')
</div>
