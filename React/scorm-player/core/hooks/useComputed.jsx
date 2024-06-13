import {useCallback} from 'react'

/**
 * USE COMPUTED HOOK
 * @param filter
 * @param deps
 * @returns
 */
const useComputed = (filter, deps = []) => {
    return useCallback(() => {
        return filter()
    }, [...deps])()
}

export default useComputed
