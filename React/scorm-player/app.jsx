import {createRoot} from 'react-dom/client'
import ScormPlayer from "./components/players/scorm";
import ScormContextWrapper from "./container/context/ScormContext";
import ReactQueryWrapper from "./container/hoc/ReactQuery";

/**
 * SCORM PLAYER WRAPPER ELEMENT
 * @type {HTMLElement}
 */
const rootElement = document.getElementById('preplab-scorm-player')

/**
 * INITIALIZATION
 */
if (rootElement) {
    createRoot(rootElement).render(
        <ReactQueryWrapper>
            <ScormContextWrapper>
                <ScormPlayer/>
            </ScormContextWrapper>
        </ReactQueryWrapper>)
}
