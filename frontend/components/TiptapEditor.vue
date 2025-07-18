<template>
  <div class="editor space-y-4">
    <div class="toolbar flex flex-wrap gap-2">
      <button type="button" @click="toggleBold" :class="{ active: editor?.isActive('bold') }" title="Bold">
        <i class="fas fa-bold"></i>
      </button>
      <button type="button" @click="toggleItalic" :class="{ active: editor?.isActive('italic') }" title="Italic">
        <i class="fas fa-italic"></i>
      </button>
      <button type="button" @click="toggleUnderline" :class="{ active: editor?.isActive('underline') }" title="Underline">
        <i class="fas fa-underline"></i>
      </button>
      <button type="button" @click="toggleStrike" :class="{ active: editor?.isActive('strike') }" title="Strikethrough">
        <i class="fas fa-strikethrough"></i>
      </button>
      <button type="button" @click="toggleCode" :class="{ active: editor?.isActive('code') }" title="Code">
        <i class="fas fa-code"></i>
      </button>
      <button type="button" @click="toggleHeading(1)" :class="{ active: editor?.isActive('heading', { level: 1 }) }" title="Heading 1">
        <i class="fas fa-heading"></i> 1
      </button>
      <button type="button" @click="toggleHeading(2)" :class="{ active: editor?.isActive('heading', { level: 2 }) }" title="Heading 2">
        <i class="fas fa-heading"></i> 2
      </button>
      <button type="button" @click="toggleHeading(3)" :class="{ active: editor?.isActive('heading', { level: 3 }) }" title="Heading 3">
        <i class="fas fa-heading"></i> 3
      </button>
      <button type="button" @click="setParagraph" :class="{ active: editor?.isActive('paragraph') }" title="Paragraph">
        <i class="fas fa-paragraph"></i>
      </button>
      <button type="button" @click="setTextAlign('left')" :class="{ active: editor?.isActive('textAlign', { textAlign: 'left' }) }" title="Align Left">
        <i class="fas fa-align-left"></i>
      </button>
      <button type="button" @click="setTextAlign('center')" :class="{ active: editor?.isActive('textAlign', { textAlign: 'center' }) }" title="Align Center">
        <i class="fas fa-align-center"></i>
      </button>
      <button type="button" @click="setTextAlign('right')" :class="{ active: editor?.isActive('textAlign', { textAlign: 'right' }) }" title="Align Right">
        <i class="fas fa-align-right"></i>
      </button>
      <button type="button" @click="showLinkModal" :class="{ active: editor?.isActive('link') }" title="Link">
        <i class="fas fa-link"></i>
      </button>
      <button type="button" @click="showImageModal" title="Image">
        <i class="fas fa-image"></i>
      </button>
      <button type="button" @click="insertTable" title="Insert Table">
        <i class="fas fa-table"></i>
      </button>
      <button type="button" @click="deleteTable" :disabled="!editor?.isActive('table')" title="Delete Table">
        <i class="fas fa-trash"></i>
      </button>
    </div>

    <EditorContent :editor="editor" class="custom-editor border border-gray-300 p-4 rounded min-h-[300px] prose max-w-none bg-white" />
    <div v-if="descriptionError" class="text-red-500 text-sm mt-2">{{ descriptionError }}</div>

    <!-- Link Modal -->
    <div v-if="showLinkPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Thêm đường dẫn</h3>
        <input
          v-model="linkUrl"
          type="text"
          placeholder="Nhập đường dẫn (https://example.com)"
          class="w-full p-2 border rounded mb-4"
          @keyup.enter="applyLink"
        >
        <div class="flex justify-end gap-2">
          <button @click="showLinkPopup = false" class="px-4 py-2 bg-gray-200 rounded">Hủy</button>
          <button @click="applyLink" class="px-4 py-2 bg-blue-600 text-white rounded">Lưu</button>
        </div>
        <p v-if="linkError" class="text-red-500 text-sm mt-2">{{ linkError }}</p>
      </div>
    </div>

    <!-- Image Modal -->
    <div v-if="showImagePopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Thêm hình ảnh</h3>
        <input
          v-model="imageUrl"
          type="text"
          placeholder="Nhập đường dẫn hình ảnh (https://example.com/image.jpg)"
          class="w-full p-2 border rounded mb-4"
          @keyup.enter="applyImage"
        >
        <div class="flex justify-end gap-2">
          <button @click="showImagePopup = false" class="px-4 py-2 bg-gray-200 rounded">Hủy</button>
          <button @click="applyImage" class="px-4 py-2 bg-blue-600 text-white rounded">Lưu</button>
        </div>
        <p v-if="imageError" class="text-red-500 text-sm mt-2">{{ imageError }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { Bold } from '@tiptap/extension-bold'
import { Italic } from '@tiptap/extension-italic'
import { Underline } from '@tiptap/extension-underline'
import { Strike } from '@tiptap/extension-strike'
import { Code } from '@tiptap/extension-code'
import { Heading } from '@tiptap/extension-heading'
import { BulletList } from '@tiptap/extension-bullet-list'
import { OrderedList } from '@tiptap/extension-ordered-list'
import { Link } from '@tiptap/extension-link'
import { Image } from '@tiptap/extension-image'
import { Highlight } from '@tiptap/extension-highlight'
import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableCell } from '@tiptap/extension-table-cell'
import { TableHeader } from '@tiptap/extension-table-header'
import { Paragraph } from '@tiptap/extension-paragraph'
import {TextAlign} from '@tiptap/extension-text-align'

const props = defineProps({
  modelValue: String,
})

const emit = defineEmits(['update:modelValue'])

const editor = ref(null)
const showLinkPopup = ref(false)
const showImagePopup = ref(false)
const linkUrl = ref('')
const imageUrl = ref('')
const linkError = ref('')
const imageError = ref('')
const descriptionError = ref('')

onMounted(() => {
  editor.value = new Editor({
    content: props.modelValue || '',
    extensions: [
      StarterKit.configure({
        heading: false,
        paragraph: false,
      }),
      Bold,
      Italic,
      Underline,
      Strike,
      Code,
      Heading.configure({
        levels: [1, 2, 3],
        HTMLAttributes: {
          class: 'prose-heading',
        },
      }),
      Paragraph.configure({
        HTMLAttributes: {
          class: 'prose-p',
        },
      }),
      BulletList.configure({
        HTMLAttributes: {
          class: 'list-disc pl-6',
        },
      }),
      OrderedList.configure({
        HTMLAttributes: {
          class: 'list-decimal pl-6',
        },
      }),
      Link.configure({
        openOnClick: false,
        HTMLAttributes: {
          class: 'text-blue-600 hover:underline',
          target: '_blank',
          rel: 'noopener noreferrer',
        },
      }),
      Image.configure({
        inline: false,
        allowBase64: true,
        HTMLAttributes: {
          class: 'max-w-full h-auto',
        },
      }),
      Highlight,
      Table.configure({
        resizable: true,
        HTMLAttributes: {
          class: 'border-collapse w-full my-4',
        },
      }),
      TableRow,
      TableHeader.configure({
        HTMLAttributes: {
          class: 'bg-gray-100 border border-gray-300 p-2 font-bold',
        },
      }),
      TableCell.configure({
        HTMLAttributes: {
          class: 'border border-gray-300 p-2',
        },
      }),
      TextAlign.configure({
        types: ['heading', 'paragraph'],
        alignments: ['left', 'center', 'right'],
        defaultAlignment: 'left',
      }),
    ],
    onUpdate: ({ editor }) => {
      emit('update:modelValue', editor.getHTML())
    },
  })
})

watch(() => props.modelValue, (newValue, oldValue) => {
  if (editor.value && newValue !== editor.value.getHTML()) {
    try {
      editor.value.commands.setContent(newValue || '', false)
      descriptionError.value = ''
    } catch (error) {
      descriptionError.value = 'Không thể tải mô tả sản phẩm'
      console.error('Lỗi khi đặt nội dung:', error)
    }
  }
}, { immediate: true })

onBeforeUnmount(() => {
  editor.value?.destroy()
})

const toggleBold = () => editor.value?.chain().focus().toggleBold().run()
const toggleItalic = () => editor.value?.chain().focus().toggleItalic().run()
const toggleUnderline = () => editor.value?.chain().focus().toggleUnderline().run()
const toggleStrike = () => editor.value?.chain().focus().toggleStrike().run()
const toggleCode = () => editor.value?.chain().focus().toggleCode().run()
const toggleHeading = (level) => editor.value?.chain().focus().toggleHeading({ level }).run()
const setParagraph = () => editor.value?.chain().focus().setParagraph().run()
const toggleBulletList = () => editor.value?.chain().focus().toggleBulletList().run()
const toggleOrderedList = () => editor.value?.chain().focus().toggleOrderedList().run()
const setTextAlign = (align) => editor.value?.chain().focus().setTextAlign(align).run()

const showLinkModal = () => {
  linkUrl.value = editor.value?.getAttributes('link').href || ''
  linkError.value = ''
  showLinkPopup.value = true
}

const showImageModal = () => {
  imageUrl.value = ''
  imageError.value = ''
  showImagePopup.value = true
}

const applyLink = () => {
  if (!editor.value) return

  if (!linkUrl.value) {
    editor.value.chain().focus().unsetLink().run()
    showLinkPopup.value = false
    return
  }

  try {
    new URL(linkUrl.value)
    editor.value
      .chain()
      .focus()
      .extendMarkRange('link')
      .setLink({ href: linkUrl.value, target: '_blank', rel: 'noopener noreferrer' })
      .run()
    showLinkPopup.value = false
    linkUrl.value = ''
  } catch {
    linkError.value = 'Vui lòng nhập URL hợp lệ'
  }
}

const applyImage = () => {
  if (!editor.value) return

  if (!imageUrl.value) {
    showImagePopup.value = false
    return
  }

  try {
    new URL(imageUrl.value)
    editor.value.chain().focus().setImage({ src: imageUrl.value }).run()
    showImagePopup.value = false
    imageUrl.value = ''
  } catch {
    imageError.value = 'Vui lòng nhập URL hình ảnh hợp lệ'
  }
}

const insertTable = () => {
  editor.value?.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()
}

const deleteTable = () => {
  editor.value?.chain().focus().deleteTable().run()
}
</script>

<style scoped>
.editor .toolbar button {
  background: #f9fafb;
  border: 1px solid #d1d5db;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.editor .toolbar button:hover {
  background-color: #e5e7eb;
}

.editor .toolbar button.active {
  background-color: #3b82f6;
  color: white;
}

.editor .toolbar button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.custom-editor :deep(.ProseMirror) {
  min-height: 300px;
  padding: 1rem;
  outline: none;
}

.custom-editor :deep(.ProseMirror h1) {
  font-size: 2rem;
  font-weight: bold;
  margin: 1rem 0;
}

.custom-editor :deep(.ProseMirror h2) {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0.75rem 0;
}

.custom-editor :deep(.ProseMirror h3) {
  font-size: 1.25rem;
  font-weight: bold;
  margin: 0.5rem 0;
}

.custom-editor :deep(.ProseMirror p) {
  margin: 0.5rem 0;
  line-height: 1.6;
}

.custom-editor :deep(.ProseMirror table) {
  border-collapse: collapse;
  width: 100%;
  margin: 1rem 0;
}

.custom-editor :deep(.ProseMirror th),
.custom-editor :deep(.ProseMirror td) {
  border: 1px solid #d1d5db;
  padding: 0.5rem;
}

.custom-editor :deep(.ProseMirror th) {
  background-color: #f9fafb;
  font-weight: bold;
}

.custom-editor :deep(.ProseMirror a) {
  color: #2563eb;
  text-decoration: underline;
}

.custom-editor :deep(.ProseMirror img) {
  max-width: 100%;
  height: auto;
  margin: 0.5rem 0;
}
</style>