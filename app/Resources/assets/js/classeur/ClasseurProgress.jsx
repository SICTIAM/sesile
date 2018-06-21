import React, { Component } from 'react'
import Moment from 'moment/moment'
import { translate } from 'react-i18next'
import {func, string, number, bool} from 'prop-types'


class ClasseurProgress extends Component {
    static contextTypes = {
        t: func
    }
    static defaultProps = {
        edit: false
    }
    render() {
        const { creation, validation, status, edit } = this.props
        const creationDate = Moment(creation)
        const validationDate = Moment(validation)
        const diffToday = validationDate.diff(Moment(), 'days')
        const diffToCreation = validationDate.diff(creationDate, 'days')

        let classProgress, percentProgress

        if(this.props.classeur.status === 2) {
            classProgress = "disabled"
        } else if (diffToday < 0) {
            classProgress = "alert"
            percentProgress = 100
        } else if (diffToday < 1) {
            classProgress = "warning"
            percentProgress = 100
        } else {
            classProgress = "success"
            percentProgress = 100 - diffToday / diffToCreation * 100
        }

        return (
            <div className="grid-x classeur-progress">
                <div className="cell small-12">
                    <div className={`text-${classProgress} text-left`}>
                        <span className="text-bold">{Moment(validationDate).format('L')}</span>
                    </div>
                </div>
            </div>
        )
    }
}

ClasseurProgress.PropTypes = {
    creation: string.isRequired,
    validation: string.isRequired,
    status: number,
    edit: bool
}

export default translate(['sesile'])(ClasseurProgress)