import ScormService from "../services/scorm.service"
import {useCallback, useEffect, useState} from "react"
import scormVersionsConstant from "../constant/scorm-versions.constant";

const useScormApi = (activeSco, scormVersion, trackingId, trackingCallback, progress = null) => {

    /**
     * STATE
     */
    const [playerReady, setPlayerReady] = useState(false)

    /**
     * INIT SCORM SCO
     */
    const lmsInit = useCallback(() => {
        return true
    }, [activeSco])

    /**
     * LMS FINISH CALL BACK
     * @returns {boolean}
     */
    const lmsFinish = useCallback(() => {
        console.log('finished')
        return true
    }, [activeSco])

    /**
     *
     * @param key
     * @returns {string}
     */
    const lmsGetValue = useCallback((key) => {
        return progress && progress[key] || ""
    }, [activeSco])

    /**
     *
     * @type {function(*, *): string}
     */
    const lmsSetValue = useCallback((key, value) => {
        if (activeSco) {
            const data = {}
            data[key] = value
            trackingCallback(data)
        }
        // console.log({key,value})
        return ""
    }, [activeSco, trackingCallback])

    /**
     *
     * @type {function(*): boolean}
     */
    const lmsCommit = useCallback((commitInput) => {
        console.log({commitInput})
        return true
    }, [activeSco])

    /**
     *
     * @returns {number}
     */
    const lmsGetLastError = useCallback(() => {
        return 0
    }, [activeSco])

    /**
     *
     * @type {function(*): any}
     */
    const lmsGetDiagnostic = useCallback((errorDiagnostic) => {
        console.log({errorDiagnostic})
        return errorDiagnostic
    }, [activeSco])

    /**
     *
     * @type {function(*): any}
     */
    const lmsGetErrorString = useCallback((errorString) => {
        console.log({errorString})
        return errorString
    }, [activeSco])

    /**
     * INITIALIZE SCORM 2004
     */
    const initializeScorm2004 = async () => {
        const API = {}
        API.Initialize = lmsInit
        API.Terminate = lmsFinish
        API.GetValue = lmsGetValue
        API.SetValue = lmsSetValue
        API.Commit = lmsCommit
        API.GetLastError = lmsGetLastError
        API.GetErrorString = lmsGetErrorString
        API.GetDiagnostic = lmsGetDiagnostic
        window.API_1484_11 = API
    }

    /**
     * INIT SCORM 12
     */
    const initializeScorm12 = async () => {
        const API = {}
        API.LMSInitialize = lmsInit
        API.LMSFinish = lmsFinish
        API.LMSGetValue = lmsGetValue
        API.LMSSetValue = lmsSetValue
        API.LMSCommit = lmsCommit
        API.LMSGetLastError = lmsGetLastError
        API.LMSGetErrorString = lmsGetErrorString
        API.LMSGetDiagnostic = lmsGetDiagnostic
        window.API = API
    }

    /**
     * INITIALIZING SCORM PLAYER
     */
    useEffect(() => {
        setPlayerReady(false)
        if (scormVersion === scormVersionsConstant.SCORM_2004) {
            initializeScorm2004().then(() => {
                setPlayerReady(true)
            })
        } else if (scormVersion === scormVersionsConstant.SCORM_12) {
            initializeScorm12().then(() => {
                setPlayerReady(true)
            })
        }
    }, [activeSco, trackingCallback]);

    return {
        isPlayerReady: playerReady
    }
}

export default useScormApi
