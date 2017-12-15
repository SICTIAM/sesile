import React, { Component } from 'react'
import Moment from 'moment/moment'
import { translate } from 'react-i18next'
import {func, string} from 'prop-types'


class ClasseurProgress extends Component {

    static contextTypes = {
        t: func
    }

    render() {

        const { t } = this.context
        const { creation, validation } = this.props
        const creationDate = Moment(creation)
        const validationDate = Moment(validation)
        const diffToday = validationDate.diff(Moment(), 'days')
        const diffToCreation = Moment().diff(creationDate, 'days')

        let classProgress, percentProgress

        if (diffToday < 0) {
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
            <div className="classeur-progress">
                <span className={"text-" + classProgress}>{t('classeur.deadline')}&nbsp;
                    <span className="text-bold">{Moment(validationDate).format('L')}</span>
                </span>

                <div className={classProgress +" progress"}>
                    <div className="progress-meter" style={{width: percentProgress + '%'}}></div>
                </div>
            </div>
        )
    }
}

ClasseurProgress.PropTypes = {
    creation: string.isRequired,
    validation: string.isRequired
}

export default translate(['sesile'])(ClasseurProgress)