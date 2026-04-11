<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: [String, Number],
    label: String,
    error: String,
    type: {
        type: String,
        default: 'text',
    },
    placeholder: String,
    required: Boolean,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="form-control w-full">
        <label v-if="label" class="label">
            <span class="label-text font-semibold">{{ label }} <span v-if="required" class="text-error">*</span></span>
        </label>
        <input
            :type="type"
            :placeholder="placeholder"
            class="input input-bordered w-full"
            :class="{ 'input-error': error }"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            ref="input"
        />
        <label v-if="error" class="label">
            <span class="label-text-alt text-error">{{ error }}</span>
        </label>
    </div>
</template>
