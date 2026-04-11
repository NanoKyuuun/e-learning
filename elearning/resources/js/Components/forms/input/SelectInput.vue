<script setup>
defineProps({
    modelValue: [String, Number],
    label: String,
    error: String,
    options: Array, // Array of { value: '', label: '' }
    required: Boolean,
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="form-control w-full">
        <label v-if="label" class="label">
            <span class="label-text font-semibold">{{ label }} <span v-if="required" class="text-error">*</span></span>
        </label>
        <select
            class="select select-bordered w-full"
            :class="{ 'select-error': error }"
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option disabled value="">Pilih opsi...</option>
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <label v-if="error" class="label">
            <span class="label-text-alt text-error">{{ error }}</span>
        </label>
    </div>
</template>
