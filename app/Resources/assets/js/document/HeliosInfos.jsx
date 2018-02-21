import React, { Component } from 'react'
import {func, object} from 'prop-types'
import {Select} from '../_components/Form'
import { translate } from 'react-i18next'

class HeliosInfos extends Component {

    constructor(props) {
        super(props)
        this.state = {}
    }

    static contextTypes = { t: func }

    componentDidMount () { }

    render () {
        const { t } = this.context
        const { pes, voucher, handleChangeVouchers } = this.props
        const ListVouchers = pes.vouchers.map(option => <option key={option.id} value={option.id}>{option.id}</option>)

        return (
            <div>
                <div className="grid-x grid-padding-x grid-margin-x">
                    <div className="cell medium-3 text-bold">{t('common.helios.voucher')}</div>

                    <Select id="vouchers"
                            value={voucher.id}
                            className="medium-6 cell"
                            onChange={handleChangeVouchers}>
                        {ListVouchers}
                    </Select>
                </div>
                <div className="grid-x grid-padding-x grid-margin-x">
                    <div className="cell medium-3 text-bold">{t('common.helios.budget')}</div>
                    <div className="cell medium-9">{ pes.budget }</div>
                </div>
                <div className="grid-x grid-padding-x grid-margin-x">
                    <div className="cell medium-3 text-bold">{t('common.helios.signing')}</div>
                    <div className="cell medium-9">
                        { pes.signataire ? pes.signataire : t('common.helios.no_signing') }
                    </div>
                </div>
            </div>
        )
    }
}

HeliosInfos.propsType = {
    pes: object.isRequired,
    voucher: object.isRequired,
    handleChangeVouchers: func
}

export default translate(['sesile'])(HeliosInfos)