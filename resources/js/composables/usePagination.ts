import { reactive, watch } from 'vue'

interface PaginationMeta {
  currentPage: number
  lastPage: number
  total: number
  perPage: number
}

export function usePagination(fetchFn: (page: number) => void, perPage = 10) {
  const meta = reactive<PaginationMeta>({
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage,
  })

  // Auto-fetch ketika halaman berubah
  watch(() => meta.currentPage, (newPage) => {
    fetchFn(newPage)
  })

  function setMeta(data: { current_page: number; last_page: number; total: number; per_page: number }) {
    meta.currentPage = data.current_page
    meta.lastPage = data.last_page
    meta.total = data.total
    meta.perPage = data.per_page
  }

  function goToPage(page: number) {
    if (page >= 1 && page <= meta.lastPage) {
      meta.currentPage = page
    }
  }

  function reset() {
    meta.currentPage = 1
  }

  return { meta, setMeta, goToPage, reset }
}
