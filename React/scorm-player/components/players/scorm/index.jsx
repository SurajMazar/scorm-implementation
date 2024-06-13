import React from 'react'
import useScormContext from "../../../core/hooks/useScormContext";
import useScormApi from "../../../core/hooks/useScormApi";
import './index.scss';
import {useMutation} from "react-query";
import ScormService from "../../../core/services/scorm.service";

const ScormPlayer = () => {

    /**
     * HOOKS
     */
    const {
        activeSco,
        scormVersion,
        trackingId,
        scormTracking,
        setShowErrorModal,
        removeScormDetails,
        setErrorMessage
    } = useScormContext()

    /**
     * SCORM PROGRESS TRACKING FUNCTION
     */
    const trackProgress = useMutation(
        [{scormVersion, activeSco: activeSco?.uuid, trackingId}], function (data) {
            if (activeSco?.uuid) {
                return ScormService.trackProgress(activeSco?.uuid, scormVersion, data, trackingId)
            }
        }, {
            onError(error) {
                if (error?.response?.status === 401) {
                    setShowErrorModal(true)
                    setErrorMessage(window?.scorm_translations?.session_expired || 'Session expired !')
                    removeScormDetails()
                }
            }
        })

    const {isPlayerReady} = useScormApi(activeSco, scormVersion, trackingId, trackProgress.mutate, scormTracking)

    return (
        <>
            {
                activeSco?.entry_url && isPlayerReady ?
                    <iframe src={activeSco?.entry_url} className={'prepr-labs-scorm-player-iframe'}/> : ''
            }
        </>
    )
}

export default ScormPlayer
