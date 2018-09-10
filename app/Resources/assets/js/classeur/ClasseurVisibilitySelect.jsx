import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'

import { Select } from '../_components/Form'

class ClasseurVisibilitySelect extends Component {
    static contextTypes = {
        t: func
    }
    static defaultProps = {
        visibility: '',
        disabled: false
    }

    render() {
        const visibilitiesStatus = ["Privé", "Public", "Privé a partir de moi", "Circuit de validation"]
        const listVisibilities = visibilitiesStatus.map((visibilite, key) =>
            <option key={key} value={key}>{visibilite}</option>
        )

        return (
            <Select
                id="visibilite"
                className={this.props.className}
                label={this.props.label}
                value={this.props.visibilite}
                disabled={this.props.disabled}
                onChange={this.props.handleChangeClasseur}>
                {listVisibilities}
            </Select>
        )
    }

}

export default translate('sesile')(ClasseurVisibilitySelect)