import React, { Component } from 'react'
import { func, array, number, string } from 'prop-types'
import { translate } from 'react-i18next'

class ClasseurStatus extends Component {
    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
    }

    render() {
        const { t } = this.context
        const { status, className } = this.props

        let statusName
        switch (status) {
            case 0:
                statusName = "refuse"
                break
            case 1:
                statusName = "pending"
                break
            case 2:
                statusName = "finish"
                break
            case 3:
                statusName = "remote"
                break
            case 4:
                statusName = "retract"
                break
        }

        return (
            <span className={className}>{statusName && t('common.classeurs.status.' + statusName)}</span>
        )
    }
}

ClasseurStatus.propsType = {
    status: number,
    className: string
}

export default translate(['sesile'])(ClasseurStatus)