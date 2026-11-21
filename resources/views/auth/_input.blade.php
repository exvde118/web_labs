<div class="mb-3">
    <label class="form-label" for="{{ $field }}">{{ $label }}</label>
    <input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}"
        type="{{ $type }}" autocomplete="{{ $autocomplete }}"
        @if ($type !== 'password') value="{{ old($field, $value ?? '') }}" @endif
        @if (isset($hint)) aria-describedby="{{ $field }}-hint" @endif required>
    @if (isset($hint))
        <div id="{{ $field }}-hint" class="form-text">{{ $hint }}</div>
    @endif
    @error($field)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
