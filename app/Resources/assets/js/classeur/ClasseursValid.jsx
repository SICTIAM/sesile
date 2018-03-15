import React, { Component } from 'react'
import { number, func } from 'prop-types'
import Classeurs from './Classeurs'
import { translate } from 'react-i18next'

class ClasseursValid extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props);
    }

    render(){
        const { t } = this.context

        return (
            <div className="grid-x grid-margin-x grid-padding-x grid-padding-y align-center-middle">
                <div className="cell medium-12 head-list-classeurs">
                    <Classeurs url="sesile_classeur_classeurapi_valid" userId={this.props.userId} user={this.props.user} />
                </div>
            </div>
        )
    }
}

ClasseursValid.PropTypes = {
    userId: number
}

export default translate(['sesile'])(ClasseursValid)