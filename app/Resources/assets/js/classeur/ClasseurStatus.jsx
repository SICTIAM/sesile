import React, { Component } from 'react'
import { func, number, string } from 'prop-types'
import { translate } from 'react-i18next'

class ClasseurStatus extends Component {
    static contextTypes = {
        t: func
    }
    static defaultProps = {
        status: null
    }
    constructor(props) {
        super(props)
    }
    status = Object.freeze({
        0: 'refused',
        1: 'pending',
        2: 'finished',
        3: 'withdrawn',
        4: 'retracted'
    })
    statusColorClass = Object.freeze({
        0: 'alert',
        1: 'warning',
        2: 'success',
        3: 'secondary',
        4: 'primary'
    })
    render() {
        const { t } = this.context
        return (
            this.props.status >= 0 &&
            <span
                className={
                    `label
                    round radius
                    text-bold
                    text-uppercase
                    ${this.statusColorClass[this.props.status]}
                    ${this.props.className || ''}`}>
                {t(`common.classeurs.status.${this.status[this.props.status]}`)}
            </span>
        )
    }
}

ClasseurStatus.propsType = {
    status: number.isRequired,
    className: string
}

export default translate(['sesile'])(ClasseurStatus)