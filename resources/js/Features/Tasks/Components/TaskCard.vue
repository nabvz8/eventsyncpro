<script setup>
import { computed } from 'vue';
import { animate } from 'motion';

const props = defineProps({
    task: {
        type: Object,
        required: true,
    },
    isDragging: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['dragstart', 'dragend']);

const priorityConfig = computed(() => {
    const configs = {
        urgent: { label: 'Urgent', class: 'bg-red-950/60 text-red-400 border-red-900/40' },
        high:   { label: 'Tinggi', class: 'bg-orange-950/60 text-orange-400 border-orange-900/40' },
        medium: { label: 'Sedang', class: 'bg-amber-950/60 text-amber-400 border-amber-900/40' },
        low:    { label: 'Rendah', class: 'bg-slate-900/60 text-slate-400 border-slate-800' },
    };
    return configs[props.task.priority] || configs.medium;
});

const formattedDueDate = computed(() => {
    if (!props.task.due_date) return null;
    const d = new Date(props.task.due_date);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
});

const isOverdue = computed(() => {
    if (!props.task.due_date) return false;
    return new Date(props.task.due_date) < new Date();
});

const onDragStart = (e) => {
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('taskId', props.task.id);
    e.dataTransfer.setData('fromStatusId', props.task.status_id);
    emit('dragstart', props.task);
};

const onDragEnd = () => {
    emit('dragend', props.task);
};

// Animasi masuk saat card pertama kali dirender
const onMounted = (el) => {
    if (el) {
        animate(el, 
            { opacity: [0, 1], y: [12, 0], scale: [0.97, 1] }, 
            { duration: 0.3, easing: [0.25, 0.1, 0.25, 1] }
        );
    }
};
</script>

<template>
    <div
        :ref="onMounted"
        draggable="true"
        @dragstart="onDragStart"
        @dragend="onDragEnd"
        class="task-card group bg-slate-950 border border-slate-800 rounded-xl p-3.5 cursor-grab active:cursor-grabbing select-none transition-all hover:border-slate-700 hover:shadow-lg hover:shadow-slate-950/50"
        :class="{ 'opacity-50 scale-95 border-indigo-700': isDragging }"
        :data-task-id="task.id"
    >
        <!-- Priority Badge + Team -->
        <div class="flex items-center justify-between mb-2.5">
            <span 
                class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-bold border tracking-wide uppercase"
                :class="priorityConfig.class"
            >
                {{ priorityConfig.label }}
            </span>
            <span v-if="task.team" class="text-[10px] text-slate-500 font-medium truncate max-w-[100px]">
                {{ task.team.name }}
            </span>
        </div>

        <!-- Task Title -->
        <h4 class="text-sm font-semibold text-white leading-snug line-clamp-2 mb-2 group-hover:text-indigo-200 transition-colors">
            {{ task.title }}
        </h4>

        <!-- Description (jika ada) -->
        <p v-if="task.description" class="text-[11px] text-slate-500 leading-relaxed line-clamp-2 mb-3">
            {{ task.description }}
        </p>

        <!-- Footer: Assignee + Due Date -->
        <div class="flex items-center justify-between mt-auto pt-2 border-t border-slate-900">
            <!-- Assignee Avatar -->
            <div v-if="task.assignee" class="flex items-center gap-1.5">
                <div class="h-5 w-5 rounded-full bg-indigo-900 border border-indigo-700 flex items-center justify-center text-[9px] font-bold text-indigo-200 uppercase">
                    {{ task.assignee.full_name.charAt(0) }}
                </div>
                <span class="text-[10px] text-slate-500">{{ task.assignee.full_name.split(' ')[0] }}</span>
            </div>
            <div v-else class="flex items-center gap-1">
                <div class="h-5 w-5 rounded-full bg-slate-900 border border-dashed border-slate-700 flex items-center justify-center">
                    <svg class="w-2.5 h-2.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>

            <!-- Due Date -->
            <span 
                v-if="formattedDueDate"
                class="text-[10px] font-semibold px-1.5 py-0.5 rounded border"
                :class="isOverdue ? 'text-red-400 border-red-900/40 bg-red-950/40' : 'text-slate-500 border-slate-800 bg-slate-900'"
            >
                {{ formattedDueDate }}
            </span>
        </div>
    </div>
</template>
