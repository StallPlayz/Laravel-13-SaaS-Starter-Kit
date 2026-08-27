<script setup lang="ts">
import { ref, computed } from 'vue'
import { Check, ChevronsUpDown } from '@lucide/vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'

const props = defineProps<{
  modelValue: string
  options: { value: string; label: string }[]
  placeholder?: string
  emptyText?: string
  disabled?: boolean
  name?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'blur': []
}>()

const open = ref(false)

const selectedLabel = computed(() => {
  const selected = props.options.find((option) => option.value === props.modelValue)
  return selected ? selected.label : props.placeholder || 'Select option...'
})

const handleSelect = (value: string) => {
  emit('update:modelValue', value)
  open.value = false
  emit('blur')
}

const handleOpenChange = (isOpen: boolean) => {
  open.value = isOpen
  if (!isOpen) {
    emit('blur')
  }
}
</script>

<template>
  <input v-if="name" type="hidden" :name="name" :value="modelValue" />
  <Popover :open="open" @update:open="handleOpenChange">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        role="combobox"
        :aria-expanded="open"
        class="w-full justify-between"
        :class="!modelValue && 'text-muted-foreground'"
        :disabled="disabled"
      >
        <span class="truncate">{{ selectedLabel }}</span>
        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-full p-0" align="start">
      <Command>
        <CommandInput :placeholder="`Search ${placeholder?.toLowerCase() || 'option'}...`" />
        <CommandEmpty>{{ emptyText || 'No option found.' }}</CommandEmpty>
        <CommandList>
          <CommandGroup>
            <CommandItem
              v-for="option in options"
              :key="option.value"
              :value="option.value"
              @select="handleSelect(option.value)"
            >
              <Check
                :class="cn(
                  'mr-2 h-4 w-4',
                  modelValue === option.value ? 'opacity-100' : 'opacity-0'
                )"
              />
              {{ option.label }}
            </CommandItem>
          </CommandGroup>
        </CommandList>
      </Command>
    </PopoverContent>
  </Popover>
</template>
