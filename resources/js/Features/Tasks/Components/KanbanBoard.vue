<script setup>
import { ref, onMounted } from 'vue';
import { useTaskStore } from '@/Features/Tasks/stores/useTaskStore';
import TaskCard from './TaskCard.vue';
import { animate, stagger } from 'motion';

const props = defineProps({
    boardData: {
        type: Array,
        required: true,
    },
    canCreateTask: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['add-task']);

const taskStore = useTaskStore();
const draggingTask = ref(null);
const dragOverColumnId = ref(null);

onMounted(() => {
    taskStore.initBoard(props.boardData);
    
    // Animasi kolom masuk secara stagger
    const columns = document.querySelectorAll('.kb-column');
    if (columns.length > 0) {
        animate(columns, { opacity: [0, 1], y: [24, 0] }, { delay: stagger(0.1), duration: 0.45, easing: 'ease-out' });
    }
});

// ─── Drag & Drop Handlers ─────────────────────────────────────────────────────

const onDragStart = (task) => {
    draggingTask.value = task;
};

const onDragEnd = () => {
    draggingTask.value = null;
    dragOverColumnId.value = null;
};

const onDragOver = (e, columnId) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    dragOverColumnId.value = columnId;
};

const onDragLeave = () => {
    dragOverColumnId.value = null;
};

const onDrop = async (e, toStatusId) => {
    e.preventDefault();
    const taskId = e.dataTransfer.getData('taskId');
    const fromStatusId = e.dataTransfer.getData('fromStatusId');
    dragOverColumnId.value = null;

    if (!taskId || fromStatusId === toStatusId) return;

    const toCol = taskStore.columns.find(c => c.id === toStatusId);
    const newPosition = toCol ? toCol.tasks.length : 0;

    await taskStore.moveTask(taskId, fromStatusId, toStatusId, newPosition);
};
</script>

<template>
    <div class="kanban-board flex gap-5 overflow-x-auto pb-4 -mx-1 px-1">
        <!-- Kanban Columns -->
        <div
            v-for="column in taskStore.columns"
            :key="column.id"
            class="kb-column flex-shrink-0 w-72 flex flex-col rounded-2xl border opacity-0 transition-colors duration-200"
            :class="[
                dragOverColumnId === column.id 
                    ? 'border-indigo-600 bg-indigo-950/20' 
                    : 'border-slate-800 bg-slate-900/40'
            ]"
            @dragover="onDragOver($event, column.id)"
            @dragleave="onDragLeave"
            @drop="onDrop($event, column.id)"
        >
            <!-- Column Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span 
                        class="h-2.5 w-2.5 rounded-full flex-shrink-0" 
                        :style="{ backgroundColor: column.color_hex }"
                    ></span>
                    <span class="font-bold text-[11px] uppercase tracking-wider text-slate-300">
                        {{ column.name }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-950 text-slate-500 border border-slate-850">
                        {{ column.tasks.length }}
                    </span>
                    <!-- Tombol Tambah Task per kolom -->
                    <button
                        v-if="canCreateTask"
                        @click="emit('add-task', column.id)"
                        class="h-5 w-5 rounded-md flex items-center justify-center text-slate-500 hover:text-white hover:bg-slate-800 transition-colors"
                        title="Tambah task"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Drop Zone Area -->
            <div 
                class="flex-1 flex flex-col gap-2.5 p-3 min-h-[200px]"
                :class="{ 'bg-indigo-950/10': dragOverColumnId === column.id }"
            >
                <!-- Task Cards -->
                <TaskCard
                    v-for="task in column.tasks"
                    :key="task.id"
                    :task="task"
                    :is-dragging="draggingTask?.id === task.id"
                    @dragstart="onDragStart"
                    @dragend="onDragEnd"
                />

                <!-- Empty State -->
                <div 
                    v-if="column.tasks.length === 0"
                    class="flex-1 flex flex-col items-center justify-center py-8 rounded-xl border border-dashed transition-colors duration-200"
                    :class="dragOverColumnId === column.id ? 'border-indigo-600/50 bg-indigo-950/20' : 'border-slate-800'"
                >
                    <div class="h-8 w-8 rounded-full bg-slate-900 flex items-center justify-center mb-2">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-semibold text-slate-600">Tidak ada tugas</span>
                    <span v-if="dragOverColumnId === column.id" class="text-[10px] text-indigo-400 mt-1">Lepaskan untuk memindahkan</span>
                </div>
            </div>
        </div>

        <!-- Empty Board State -->
        <div 
            v-if="taskStore.columns.length === 0"
            class="flex-1 flex flex-col items-center justify-center py-16 text-center"
        >
            <p class="text-sm text-slate-500">Board Kanban belum tersedia.</p>
        </div>
    </div>
</template>
