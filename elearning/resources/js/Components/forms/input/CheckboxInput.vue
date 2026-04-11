<script setup>
defineProps({
    modelValue: Boolean,
    label: String,
    error: String,
    required: Boolean,
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="form-control">
        <label class="label cursor-pointer justify-start gap-3">
            <input
                type="checkbox"
                class="checkbox checkbox-primary"
                :class="{ 'checkbox-error': error }"
                :checked="modelValue"
                @change="$emit('update:modelValue', $event.target.checked)"
            />
            <span v-if="label" class="label-text font-semibold">{{ label }} <span v-if="required" class="text-error">*</span></span>
        </label>
        <label v-if="error" class="label py-0">
            <span class="label-text-alt text-error">{{ error }}</span>
        </label>
    </div>
</template>
