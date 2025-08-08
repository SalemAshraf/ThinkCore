<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label ? $label : $name }}</label>
    <input type="text" placeholder="{{ $placeholder }}" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}" class="form-control" required>
    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
