      <!-- Global Delete Modal -->
      <div x-show="showModal" class="fixed inset-0 z-99999 flex items-center justify-center bg-black/50 px-4" x-transition
          style="display: none;">
          <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg">
              <h2 class="text-lg font-semibold text-gray-800">Delete Confirmation</h2>
              <p class="mt-2 text-sm text-gray-600">
                  Are you sure you want to delete this data? Once deleted, it cannot be restored.
              </p>


              <div class="mt-6 flex justify-end gap-3">
                  <button @click="showModal = false"
                      class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                      Cancel
                  </button>
                  <form :action="deleteUrl" method="POST" enctype="multipart/form-data">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                          class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                          Delete
                      </button>
                  </form>
              </div>
          </div>
      </div>
