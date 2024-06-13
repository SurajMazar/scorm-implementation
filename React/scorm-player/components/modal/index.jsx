import React from 'react'
import {createPortal} from "react-dom";
import './index.scss'
const Modal = (props) => {

    /**
     * COMPONENT PROPS
     */
    const {children} = props

    return createPortal(<>
        <div className="scorm-modal">
            <div className="scorm-modal--content">
                {children}
            </div>
        </div>
    </>, document.body);
}

export default Modal
