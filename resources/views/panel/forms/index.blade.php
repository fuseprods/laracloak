@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Form Management') }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.forms.create') }}" class="btn btn-primary">
                <span>➕</span> {{ __('Create Form') }}
            </a>
        </div>
    </div>

    <div class="content-section full-width">
        <form method="GET" action="{{ route('panel.forms.index') }}"
            style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="flex: 1;">
                <input type="text" name="search" placeholder="{{ __('Search forms by slug or destination...') }}"
                    value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Slug / Public URL') }}</th>
                        <th>{{ __('Destination (POST)') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $form)
                        <tr>
                            <td style="font-weight: 500;">
                                <div>{{ $form->slug }}</div>
                                <small style="color: var(--text-muted); font-family: monospace;">
                                    <a href="{{ route('front.show', $form->slug) }}" target="_blank"
                                        style="color: inherit; text-decoration: none;">
                                        /front/{{ $form->slug }} ↗
                                    </a>
                                </small>
                            </td>
                            <td style="color: var(--text-muted); font-family: monospace; font-size: 0.85rem;">
                                {{ Str::limit($form->destination_url, 40) }}
                            </td>
                            <td>
                                @if($form->is_published)
                                    <span class="badge badge-admin">{{ __('Published') }}</span>
                                @else
                                    <span class="badge badge-user">{{ __('Draft') }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.75rem;">
                                    <a href="{{ route('front.show', $form->slug) }}" target="_blank" class="btn btn-sm"
                                        style="background: var(--bg-dark); border: 1px solid var(--border);">
                                        {{ __('Preview') }}
                                    </a>
                                    <a href="{{ route('panel.forms.edit', $form) }}" class="btn btn-sm btn-primary">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('panel.forms.destroy', $form) }}" method="POST"
                                        onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                                {{ __('No forms found. Start by creating a new outbound form.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $forms->links() }}
        </div>
    </div>
@endsection