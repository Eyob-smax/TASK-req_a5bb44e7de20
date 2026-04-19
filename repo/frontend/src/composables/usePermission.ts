import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { RoleName } from '@/types'

export function usePermission() {
  const auth = useAuthStore()

  function can(role: RoleName | RoleName[], scopeType?: string, scopeId?: number): boolean {
    const roles = Array.isArray(role) ? role : [role]
    if (!auth.isAuthenticated) return false

    return roles.some((r) => {
      const match = (auth.user?.roles ?? []).find((ur) => ur.name === r)
      if (!match) return false
      if (!scopeType) return true
      if (match.scope_type !== scopeType) return false
      if (scopeId !== undefined && match.scope_id !== scopeId) return false
      return true
    })
  }

  const isAdmin     = computed(() => can('administrator'))
  const isRegistrar = computed(() => can('registrar'))
  const isTeacher   = computed(() => can('teacher'))
  const isStudent   = computed(() => can('student'))

  function canManageSection(sectionId: number): boolean {
    return can('administrator') || can('teacher', 'section', sectionId)
  }

  function canImportRosterForTerm(termId: number): boolean {
    return can('administrator') || can('registrar', 'term', termId)
  }

  function canModerate(): boolean {
    return can(['administrator', 'registrar'])
  }

  return { can, isAdmin, isRegistrar, isTeacher, isStudent, canManageSection, canImportRosterForTerm, canModerate }
}
