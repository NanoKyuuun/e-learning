<script setup>
defineProps({
    modelValue: [String, Number],
    label: String,
    error: String,
    placeholder: String,
    required: Boolean,
    rows: {
        type: Number,
        default: 3
    }
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="form-control w-full">
        <label v-if="label" class="label">
            <span class="label-text font-semibold">{{ label }} <span v-if="required" class="text-error">*</span></span>
        </label>
        <textarea
            :placeholder="placeholder"
            class="textarea textarea-bordered w-full"
            :class="{ 'textarea-error': error }"
            :value="modelValue"
            :rows="rows"
            @input="$emit('update:modelValue', $event.target.value)"
        ></textarea>
        <label v-if="error" class="label">
            <span class="label-text-alt text-error">{{ error }}</span>
        </label>
    </div>
</template>
