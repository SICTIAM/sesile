import React, { Component } from 'react'
import PropTypes, { func, string } from 'prop-types'
import RichTextEditor from 'react-rte'

export default class Editor extends Component {
  static propTypes = {
    id: string.isRequired,
    handleChange: func.isRequired,
    className: string,
    label: string.isRequired,
    value: string
  }
  static defaultProps = {
    className: ''
  }

  state = {
    value: RichTextEditor.createEmptyValue(),
    isSet: false
  }

  componentDidMount() {
      const value = RichTextEditor.createValueFromString(this.props.value, 'html')
      this.setState({value: value})
  }
  componentWillReceiveProps(nextProps) {
      if(!this.state.isSet && nextProps.value != null) {
        const value = RichTextEditor.createValueFromString(nextProps.value, 'html')
        this.setState({value: value, isSet: true})
      }
  }

  handleChange = (value) => {
    this.setState({value})
    this.props.handleChange(this.props.id, value.toString('html'))
  }

  render () {
    return (
        <div className={this.props.className}>
          <label className="text-bold">
              {this.props.label}
          </label>
          <RichTextEditor value={this.state.value} onChange={this.handleChange}/>
        </div>
    )
  }
}