<script setup>
defineProps({
    modelValue: String,
    label: String,
    error: String,
    required: Boolean,
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="form-control w-full">
        <label v-if="label" class="label">
            <span class="label-text font-semibold">{{ label }} <span v-if="required" class="text-error">*</span></span>
        </label>
        <input
            type="date"
            class="input input-bordered w-full"
            :class="{ 'input-error': error }"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <label v-if="error" class="label">
            <span class="label-text-alt text-error">{{ error }}</span>
        </label>
    </div>
</template>
