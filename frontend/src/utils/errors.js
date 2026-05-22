/**
 * Extract per-field errors from Laravel validation error response.
 * Returns an object keyed by field name with the first error message.
 */
export function parseErrors(error) {
  const result = {}
  if (error.response?.status === 422 && error.response.data?.errors) {
    const errors = error.response.data.errors
    for (const field in errors) {
      const key = field.includes('.') ? field.split('.')[0] : field
      result[key] = Array.isArray(errors[field]) ? errors[field][0] : errors[field]
    }
  }
  return result
}

/**
 * Get a single field error from a parsed errors object.
 */
export function fieldError(errors, field) {
  return errors[field] || ''
}
