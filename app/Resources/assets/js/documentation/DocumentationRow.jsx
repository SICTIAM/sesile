import React, { Component } from 'react'
import { func, object, string } from 'prop-types'
import { translate } from 'react-i18next'
import { Link } from 'react-router-dom'
import Moment from 'moment'
import {DisplayLongText} from "../_utils/Utils"
import {Cell} from "../_components/UI";

class DocumentationRow extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props);
    }

    render() {

        const { documentation, download_route } = this.props
        const { t } = this.context

        return (
            <div className="HelpBlock" onClick={() => window.open(Routing.generate(download_route, {id: documentation.id}), "_blank")}>
                <div className="text-bold"><DisplayLongText text={documentation.description} maxSize={30}/></div>
                <div style={{display:"flex", marginTop:"0.5em"}}>
                    <div className="align-left" style={{width:"92%"}}>{ Moment(documentation.date).format('LL') }</div>
                    <div className="align-right">{ documentation.version }</div>
                </div>
            </div>
        )
    }

}

DocumentationRow.PropTypes = {
    documentation: object.isRequired,
    download_route: string.isRequired
}

export default translate(['sesile'])(DocumentationRow)