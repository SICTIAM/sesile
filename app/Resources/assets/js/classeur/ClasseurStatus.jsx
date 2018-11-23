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
        4: 'retracted',
        5: 'progressSign'
    })
    statusColorClass = Object.freeze({
        0: 'alert',
        1: 'warning',
        2: 'success',
        3: 'secondary',
        4: 'primary',
        5: 'progressSign'
    })
    render() {
        const { t } = this.context
        return (
            this.props.status >= 0 &&
            <div
                className={`ui ${this.statusColorClass[this.props.status]} ribbon label labelStatus`}
                style={{color: '#fff'}}>
                {t(`common.classeurs.status.${this.status[this.props.status]}`)}
                {this.props.status === 5 &&
                    <i className='fa fa-spinner fa-spin' style={{fontSize: '1.1em', color: '#ffff', marginLeft: '5px'}} />}
            </div>
        )
    }
}

ClasseurStatus.propsType = {
    status: number.isRequired,
    className: string
}

export default translate(['sesile'])(ClasseurStatus)