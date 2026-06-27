import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useTaskStore = defineStore('tasks', () => {
    /** @type {import('vue').Ref<Array>} */
    const columns = ref([]) // Array of { id, name, color_hex, position, tasks: [...] }
    const isLoading = ref(false)

    /**
     * Inisialisasi board dari data yang dikirim server (Inertia props).
     * @param {Array} boardData - Array kolom status dari server
     */
    function initBoard(boardData) {
        columns.value = boardData.map(col => ({
            ...col,
            tasks: col.tasks ? [...col.tasks] : []
        }))
    }

    /**
     * Memindahkan task secara optimistic ke kolom baru.
     * @param {string} taskId - ID task yang dipindahkan
     * @param {string} fromStatusId - ID kolom asal
     * @param {string} toStatusId - ID kolom tujuan
     * @param {number} newPosition - Posisi baru dalam kolom tujuan
     */
    async function moveTask(taskId, fromStatusId, toStatusId, newPosition) {
        // Temukan task di kolom asal
        const fromCol = columns.value.find(c => c.id === fromStatusId)
        const toCol = columns.value.find(c => c.id === toStatusId)
        if (!fromCol || !toCol) return

        const taskIndex = fromCol.tasks.findIndex(t => t.id === taskId)
        if (taskIndex === -1) return

        const [task] = fromCol.tasks.splice(taskIndex, 1)
        const movedTask = { ...task, status_id: toStatusId, position: newPosition }
        toCol.tasks.splice(newPosition, 0, movedTask)

        // Re-index posisi di kolom tujuan
        toCol.tasks.forEach((t, idx) => { t.position = idx })

        try {
            // Ambil CSRF token dari cookie
            const token = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1]
            await axios.put(route('tasks.update', { taskId }), {
                status_id: toStatusId,
                position: newPosition,
            }, {
                headers: {
                    'X-XSRF-TOKEN': token ? decodeURIComponent(token) : '',
                }
            })
        } catch (error) {
            // Rollback jika gagal
            toCol.tasks.splice(newPosition, 1)
            fromCol.tasks.splice(taskIndex, 0, task)
            console.error('Gagal memindahkan task:', error)
        }
    }

    /**
     * Tambahkan task baru ke kolom yang sesuai setelah pembuatan berhasil.
     * @param {Object} task - Data task dari server
     */
    function addTaskToBoard(task) {
        const col = columns.value.find(c => c.id === task.status_id)
        if (col) {
            col.tasks.push(task)
        }
    }

    /**
     * Hitung total task di seluruh papan.
     */
    const totalTasks = computed(() =>
        columns.value.reduce((sum, col) => sum + col.tasks.length, 0)
    )

    return {
        columns,
        isLoading,
        initBoard,
        moveTask,
        addTaskToBoard,
        totalTasks,
    }
})
