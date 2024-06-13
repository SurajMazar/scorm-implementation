import {createContext, useState} from "react";
import {useQuery} from "react-query";
import ScormService from "../../core/services/scorm.service";
import useComputed from "../../core/hooks/useComputed";
import Modal from "../../components/modal";

/**
 * Scorm context
 */
export const ScormContext = createContext({})


const ScormContextWrapper = ({children}) => {

    /**
     * COMPONENT STATE
     */
    const [scormUUID] = useState(window?.scorm_uuid || null)
    const [activeSco, setActiveSco] = useState(null);
    const [showErrorModal, setShowErrorModal] = useState(false)
    const [errorMessage, setErrorMessage] = useState(null)

    /**
     * TRACKING ID
     */
    const trackingId = useComputed(() => {
        const urlSearchString = window.location.search;
        const params = new URLSearchParams(urlSearchString);
        return params.get('tracking_id')
    }, [])

    /**
     * FETCHING THE SCORM DETAILS - REACT QUERY
     */
    const {data: scormDetails, isLoading} = useQuery([{scormUUID, trackingId}], () => {
        if (scormUUID) {
            return ScormService.getDetails(scormUUID, trackingId)
        }
        return null;
    }, {
        onSuccess(data) {
            const fistsco = data?.scos[0];
            if (fistsco?.entry_url) {
                setActiveSco(data?.scos[0] || null)
            } else {
                setActiveSco(fistsco?.children[0])
            }
        },
        onError(error) {
            if(error?.response?.status === 401){
                setShowErrorModal(true)
                setErrorMessage(window?.scorm_translations?.session_expired || 'Session expired !')
            }else if(error?.response?.status === 404){
                setShowErrorModal(true)
                setErrorMessage(window?.scorm_translations?.not_found || "The scorm file doesn't exist !")
            }
        },
        retry: false,
    })

    /**
     * SCORM VERSION
     */
    const scormVersion = useComputed(() => {
        return scormDetails?.version
    }, [scormDetails])

    /**
     * SCORM PREVIOUS TRACK
     */
    const scormTracking = useComputed(() => {
        return activeSco?.tracking || null
    }, [activeSco])


    return (
        <>
            <ScormContext.Provider value={{
                scormUUID,
                scormDetails,
                isLoading,
                activeSco,
                scormVersion,
                setActiveSco,
                trackingId,
                scormTracking,
                setShowErrorModal,
                setErrorMessage
            }}>

                {/*Error modal*/}
                {
                    showErrorModal && errorMessage ?
                        <Modal>
                            {errorMessage ?? ''}
                        </Modal> :
                        <>
                            {children}
                        </>
                }
                {/*Error modal*/}
            </ScormContext.Provider>
        </>
    )
}

export default ScormContextWrapper

